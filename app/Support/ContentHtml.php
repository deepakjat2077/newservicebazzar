<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;

/**
 * Normalises the rich-text content admins write for categories, subcategories
 * and service keywords into a small, predictable set of tags.
 *
 * Almost all of this content is pasted out of Word or Google Docs, which brings
 * with it `<o:p>` tags, `mso-*` styles, hundreds of nested `<div>`s, `<font>`
 * elements and headings buried inside `<b><span>` wrappers. The front end used
 * to strip only `style=` attributes, which left the tag soup behind: every
 * category then rendered with its own accidental spacing and its own heading
 * sizes, and pasted `<font color>` runs coloured words mid-sentence.
 *
 * The rule here is that the stored markup carries structure only — headings,
 * paragraphs, lists, tables, emphasis and links — and the theme's stylesheet
 * decides what all of it looks like. That is what makes every category page
 * come out with identical rhythm regardless of who wrote it or where from.
 */
class ContentHtml
{
    /** Block tags kept as-is. */
    protected const BLOCKS = [
        'p', 'h2', 'h3', 'h4', 'h5', 'ul', 'ol', 'li', 'blockquote',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'hr',
    ];

    /** Inline tags kept as-is. */
    protected const INLINE = ['strong', 'em', 'a', 'br', 'sup', 'sub'];

    /** Inline wrappers that must never contain a block element. */
    protected const INLINE_WRAPPERS = ['b', 'strong', 'i', 'em', 'u', 'a', 'span', 'font', 'small'];

    /** Attributes still worth keeping, per tag. */
    protected const KEEP_ATTRIBUTES = [
        'a'  => ['href', 'target', 'rel'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    /** Blocks that are dropped when they end up holding nothing. */
    protected const DROP_WHEN_EMPTY = ['p', 'h2', 'h3', 'h4', 'h5', 'li', 'blockquote', 'span', 'strong', 'em'];

    /**
     * Clean one blob of admin-written HTML.
     *
     * Safe to run on content that is already clean — it is idempotent, which is
     * what lets it sit on both the save path and the render path at once.
     */
    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        $html = static::stripJunk($html);

        if (trim(strip_tags($html)) === '' && stripos($html, '<img') === false) {
            return '';
        }

        $doc = static::load($html);

        if ($doc === null) {
            // Parsing failed on something pathological; plain text beats a
            // half-parsed page, and the caller still gets readable content.
            return trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
        }

        $xpath = new DOMXPath($doc);
        $body = $doc->getElementsByTagName('body')->item(0);

        if (!$body) {
            return '';
        }

        static::unwrapInlineWrappersAroundBlocks($xpath);
        static::unwrapPresentationTags($xpath);
        static::normaliseDivs($xpath);
        static::renameTags($xpath);
        static::stripAttributes($xpath);
        static::normaliseWhitespace($xpath);
        static::promoteLooseInlineContent($body);
        static::dropEmptyNodes($xpath);

        return static::serialise($body);
    }

    /**
     * The same clean, memoised.
     *
     * The front end cleans on render as well as on save, so the content
     * already sitting in the table comes out right without every category
     * having to be opened and saved again. Parsing 25KB of Word markup on
     * every page view would not be free, so the answer is cached against a
     * hash of the input: editing the content changes the key, and nothing
     * ever has to be invalidated by hand.
     */
    public static function cleanCached(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        return \Illuminate\Support\Facades\Cache::remember(
            'content-html:' . sha1($html),
            now()->addDays(30),
            fn () => static::clean($html)
        );
    }

    /**
     * Word/Docs debris that is easier to delete before parsing than after:
     * namespaced tags, conditional comments and whole `<style>` blocks confuse
     * the parser rather than survive it.
     */
    protected static function stripJunk(string $html): string
    {
        $html = preg_replace('/<!--\[if[\s\S]*?<!\[endif\]-->/i', '', $html);
        $html = preg_replace('/<!--[\s\S]*?-->/', '', $html);
        $html = preg_replace('/<\s*(style|script|xml)\b[^>]*>[\s\S]*?<\/\s*\1\s*>/i', '', $html);

        // <o:p>, <w:sdt>, <st1:city> — Office namespace tags, opening and closing.
        $html = preg_replace('/<\/?\s*[a-z]+:[a-z0-9]+[^>]*>/i', '', $html);

        // Word hard-wraps its paragraphs, so a single logical sentence arrives
        // split across several lines. Those newlines are not content.
        $html = str_replace(["\r\n", "\r"], "\n", $html);
        $html = str_replace("\xC2\xA0", ' ', $html);
        $html = preg_replace('/&nbsp;/i', ' ', $html);

        return $html;
    }

    protected static function load(string $html): ?DOMDocument
    {
        $doc = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);

        $ok = $doc->loadHTML(
            '<?xml encoding="UTF-8"?><body>' . $html . '</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $ok ? $doc : null;
    }

    /**
     * `<b><span><h2>Heading</h2></span></b>` is how Word delivers a bold
     * heading, and it is invalid: browsers close the inline element early and
     * the heading inherits whatever is left, which is the single biggest reason
     * headings looked different from one category to the next. Whenever an
     * inline wrapper contains a block, the wrapper goes and the block stays.
     */
    protected static function unwrapInlineWrappersAroundBlocks(DOMXPath $xpath): void
    {
        $blockList = implode(' or ', array_map(
            fn ($tag) => "descendant::{$tag}",
            ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'ul', 'ol', 'li', 'table', 'blockquote']
        ));

        // Innermost first, so unwrapping one does not orphan the next.
        foreach (static::INLINE_WRAPPERS as $tag) {
            $nodes = iterator_to_array($xpath->query("//{$tag}[{$blockList}]") ?: []);

            foreach (array_reverse($nodes) as $node) {
                static::unwrap($node);
            }
        }
    }

    /** `<span>`, `<font>` and friends carry only looks, never meaning. */
    protected static function unwrapPresentationTags(DOMXPath $xpath): void
    {
        foreach (['font', 'span', 'small', 'big', 'center', 'section', 'article', 'header', 'footer'] as $tag) {
            foreach (array_reverse(iterator_to_array($xpath->query("//{$tag}") ?: [])) as $node) {
                static::unwrap($node);
            }
        }
    }

    /**
     * A `<div>` is either standing in for a paragraph or is pure nesting.
     * Wrapping divs are unwrapped; the ones actually holding a sentence become
     * real paragraphs, which is what gives the page one consistent gap between
     * blocks instead of a different one per nesting depth.
     */
    protected static function normaliseDivs(DOMXPath $xpath): void
    {
        $guard = 0;

        // Unwrapping a nest exposes the div underneath it, so keep going until
        // the document stops changing (bounded, in case of something cyclic).
        while ($guard++ < 50) {
            $divs = iterator_to_array($xpath->query('//div') ?: []);

            if (!$divs) {
                return;
            }

            $changed = false;

            foreach (array_reverse($divs) as $div) {
                if (static::hasBlockChild($div)) {
                    static::unwrap($div);
                } else {
                    static::rename($div, 'p');
                }

                $changed = true;
            }

            if (!$changed) {
                return;
            }
        }
    }

    /** Presentational tags that have a semantic equivalent. */
    protected static function renameTags(DOMXPath $xpath): void
    {
        $map = ['b' => 'strong', 'i' => 'em', 'u' => 'em', 'h1' => 'h2', 'h6' => 'h5'];

        foreach ($map as $from => $to) {
            foreach (array_reverse(iterator_to_array($xpath->query("//{$from}") ?: [])) as $node) {
                static::rename($node, $to);
            }
        }

        // Anything still outside the allow-list keeps its text and loses its
        // tag. `body` is the fragment's own container, not content — unwrapping
        // it would empty the document.
        $allowed = array_merge(static::BLOCKS, static::INLINE, ['img', 'body', 'html']);

        foreach (array_reverse(iterator_to_array($xpath->query('//body//*') ?: [])) as $node) {
            if (!in_array($node->nodeName, $allowed, true)) {
                static::unwrap($node);
            }
        }
    }

    protected static function stripAttributes(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//*') ?: [] as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $keep = static::KEEP_ATTRIBUTES[$node->nodeName] ?? [];

            foreach (iterator_to_array($node->attributes ?? []) as $attribute) {
                if (!in_array($attribute->nodeName, $keep, true)) {
                    $node->removeAttribute($attribute->nodeName);
                    continue;
                }

                // A pasted javascript: href is the one genuinely unsafe thing
                // that survives this far.
                if ($attribute->nodeName === 'href'
                    && preg_match('/^\s*(javascript|data|vbscript):/i', $attribute->nodeValue)) {
                    $node->removeAttribute('href');
                }
            }

            if ($node->nodeName === 'a' && $node->getAttribute('target') === '_blank') {
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    /**
     * Collapse the runs of spaces and newlines Word leaves inside sentences,
     * without welding two words together at a tag boundary.
     */
    protected static function normaliseWhitespace(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//text()') ?: [] as $text) {
            if (!$text instanceof DOMText) {
                continue;
            }

            $value = preg_replace('/\s+/u', ' ', $text->nodeValue);

            $text->nodeValue = $value;
        }
    }

    /**
     * Text sitting directly in the body — common once the wrapping divs are
     * gone — is wrapped in a paragraph so it picks up the same spacing as
     * everything around it instead of running into the next block.
     */
    protected static function promoteLooseInlineContent(DOMNode $body): void
    {
        $doc = $body->ownerDocument;
        $paragraph = null;

        foreach (iterator_to_array($body->childNodes) as $node) {
            $isBlock = $node instanceof DOMElement
                && in_array($node->nodeName, static::BLOCKS, true);

            if ($isBlock) {
                $paragraph = null;
                continue;
            }

            if ($node instanceof DOMText && trim($node->nodeValue) === '') {
                $body->removeChild($node);
                continue;
            }

            if ($paragraph === null) {
                $paragraph = $doc->createElement('p');
                $body->insertBefore($paragraph, $node);
            }

            $paragraph->appendChild($node);
        }
    }

    /** Blocks left holding nothing are pure vertical gap on the page. */
    protected static function dropEmptyNodes(DOMXPath $xpath): void
    {
        $guard = 0;

        while ($guard++ < 20) {
            $removed = false;
            $selector = implode(' | ', array_map(fn ($t) => "//{$t}", static::DROP_WHEN_EMPTY));

            foreach (array_reverse(iterator_to_array($xpath->query($selector) ?: [])) as $node) {
                if (trim($node->textContent) !== '') {
                    continue;
                }

                if ($node->getElementsByTagName('img')->length > 0) {
                    continue;
                }

                $node->parentNode?->removeChild($node);
                $removed = true;
            }

            if (!$removed) {
                return;
            }
        }
    }

    /** Replace a node with its own children. */
    protected static function unwrap(DOMNode $node): void
    {
        $parent = $node->parentNode;

        if (!$parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    /** Swap a node's tag, keeping its children. */
    protected static function rename(DOMNode $node, string $tag): void
    {
        $parent = $node->parentNode;

        if (!$parent) {
            return;
        }

        $replacement = $node->ownerDocument->createElement($tag);

        while ($node->firstChild) {
            $replacement->appendChild($node->firstChild);
        }

        $parent->replaceChild($replacement, $node);
    }

    protected static function hasBlockChild(DOMNode $node): bool
    {
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement
                && in_array($child->nodeName, array_merge(static::BLOCKS, ['div']), true)) {
                return true;
            }
        }

        return false;
    }

    /** One block per line, so a diff of two versions stays readable. */
    protected static function serialise(DOMNode $body): string
    {
        $out = '';

        foreach ($body->childNodes as $child) {
            $out .= $body->ownerDocument->saveHTML($child);
        }

        $out = preg_replace('/>\s+</', '><', $out);
        $out = preg_replace('/<\/(p|h2|h3|h4|h5|ul|ol|li|table|tr|blockquote)>/i', "</$1>\n", $out);
        $out = preg_replace('/\n{2,}/', "\n", $out);

        return trim($out);
    }
}
