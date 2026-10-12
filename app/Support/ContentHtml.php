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
    protected const CACHE_VERSION = 'v7'; // v7: <img> keeps src/alt/width/height

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
        // Without src an editor image was saved as a bare <img> and vanished.
        'img' => ['src', 'alt', 'width', 'height'],
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
        static::fixHeadingHierarchy($xpath);
        static::normaliseWhitespace($xpath);
        static::promoteLooseInlineContent($body);
        static::normaliseTypedBulletParagraphs($xpath);
        static::stripTypedBullets($xpath);
        static::normaliseParagraphBreakSpacers($xpath);
        static::preserveEditorSpacers($xpath);
        static::dropEmptyNodes($xpath);
        static::wrapTables($xpath);
        static::preventOrphanSlashesInTableCells($xpath);

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
            'content-html:' . static::CACHE_VERSION . ':' . sha1($html),
            now()->addDays(30),
            fn () => static::clean($html)
        );
    }

    public static function flushPublicPageCache(): void
    {
        try {
            \Illuminate\Support\Facades\Cache::store(config('cache.public_pages.store', 'file'))->flush();
        } catch (\Throwable $e) {
            report($e);
        }
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

                // Image sources: web addresses and site paths only. Embedded
                // data: images are turned into files before this runs
                // (ContentHtml::storeEmbeddedImages); anything else goes.
                if ($attribute->nodeName === 'src'
                    && !preg_match('#^\s*(https?://|/(?!/))#i', $attribute->nodeValue)) {
                    $node->removeAttribute('src');
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

    /**
     * Keep intentional blank lines from the admin editor.
     *
     * Editors usually save a user-made blank line as <p><br></p> or an empty
     * paragraph. The cleaner used to remove those paragraphs completely, so the
     * frontend ignored spacing that the admin deliberately added.
     */
    protected static function preserveEditorSpacers(DOMXPath $xpath): void
    {
        foreach (iterator_to_array($xpath->query('//body/p') ?: []) as $paragraph) {
            if (!$paragraph->parentNode || trim($paragraph->textContent) !== '') {
                continue;
            }

            if ($paragraph->getElementsByTagName('img')->length > 0) {
                continue;
            }

            $spacer = $paragraph->ownerDocument->createElement('div');
            $spacer->setAttribute('class', 'rich-content-spacer');

            $paragraph->parentNode->replaceChild($spacer, $paragraph);
        }
    }

    /**
     * Summernote can store a manual gap inside the same paragraph as
     * <br><br>. Split that into real blocks plus spacer nodes, otherwise later
     * whitespace normalisation makes the admin's gap look ignored.
     */
    protected static function normaliseParagraphBreakSpacers(DOMXPath $xpath): void
    {
        foreach (iterator_to_array($xpath->query('//body/p[br]') ?: []) as $paragraph) {
            if (!$paragraph->parentNode || trim($paragraph->textContent) === '') {
                continue;
            }

            $doc = $paragraph->ownerDocument;
            $parent = $paragraph->parentNode;
            $replacement = [];
            $current = $doc->createElement('p');
            $brRun = 0;

            $flushBreaks = function () use (&$brRun, &$replacement, $doc): void {
                if ($brRun < 2) {
                    $brRun = 0;
                    return;
                }

                for ($i = 1; $i < $brRun; $i++) {
                    $spacer = $doc->createElement('div');
                    $spacer->setAttribute('class', 'rich-content-spacer');
                    $replacement[] = $spacer;
                }

                $brRun = 0;
            };

            $pushCurrent = function () use (&$current, &$replacement, $doc): void {
                if (trim($current->textContent) === '' && $current->getElementsByTagName('img')->length === 0) {
                    $current = $doc->createElement('p');
                    return;
                }

                $replacement[] = $current;
                $current = $doc->createElement('p');
            };

            foreach (iterator_to_array($paragraph->childNodes) as $child) {
                if ($child instanceof DOMElement && $child->nodeName === 'br') {
                    $brRun++;
                    continue;
                }

                if ($brRun >= 2) {
                    $pushCurrent();
                    $flushBreaks();
                } elseif ($brRun === 1) {
                    $current->appendChild($doc->createTextNode(' '));
                    $brRun = 0;
                }

                $current->appendChild($child->cloneNode(true));
            }

            if ($brRun >= 2) {
                $pushCurrent();
                $flushBreaks();
            }

            $pushCurrent();

            if (!$replacement) {
                continue;
            }

            foreach ($replacement as $node) {
                $parent->insertBefore($node, $paragraph);
            }

            $parent->removeChild($paragraph);
        }
    }

    /**
     * Drop bullet characters people typed into their own list items.
     *
     * Content written in Word often carries a literal "• " at the start of
     * each line, and once that line is a real `<li>` the page shows two
     * bullets: the CSS one and the typed one. The same goes for a leading
     * dash, which is how the same lists arrive from a plain-text paste.
     */
    protected static function stripTypedBullets(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//body//li') ?: [] as $li) {
            // Only the first text in the item — a bullet character further in
            // is part of the sentence.
            $first = $li->firstChild;

            while ($first instanceof DOMElement && $first->firstChild) {
                $first = $first->firstChild;
            }

            if (!$first instanceof DOMText) {
                continue;
            }

            $first->nodeValue = preg_replace(
                '/^\s*[\x{2022}\x{25E6}\x{25AA}\x{2023}\x{00B7}\x{2043}\-\x{2013}\x{2014}]+\s*/u',
                '',
                $first->nodeValue
            );
        }
    }

    /**
     * Some editors save bullets as separate paragraphs that start with a typed
     * "•" instead of real <ul><li> markup. Turn those runs into actual lists so
     * the frontend can indent and space them consistently.
     */
    protected static function normaliseTypedBulletParagraphs(DOMXPath $xpath): void
    {
        $paragraphs = iterator_to_array($xpath->query('//body/p') ?: []);

        foreach ($paragraphs as $paragraph) {
            if (!$paragraph->parentNode || $paragraph->nodeName !== 'p') {
                continue;
            }

            if (!preg_match('/^\s*[\x{2022}\x{25E6}\x{25AA}\x{2023}\x{00B7}\x{2043}]\s+/u', $paragraph->textContent)) {
                continue;
            }

            $doc = $paragraph->ownerDocument;
            $parent = $paragraph->parentNode;
            $list = $doc->createElement('ul');
            $cursor = $paragraph;

            while ($cursor instanceof DOMElement
                && $cursor->nodeName === 'p'
                && preg_match('/^\s*[\x{2022}\x{25E6}\x{25AA}\x{2023}\x{00B7}\x{2043}]\s+/u', $cursor->textContent)) {
                $next = $cursor->nextSibling;
                $item = $doc->createElement('li');

                while ($cursor->firstChild) {
                    $item->appendChild($cursor->firstChild);
                }

                $first = $item->firstChild;

                while ($first instanceof DOMElement && $first->firstChild) {
                    $first = $first->firstChild;
                }

                if ($first instanceof DOMText) {
                    $first->nodeValue = preg_replace(
                        '/^\s*[\x{2022}\x{25E6}\x{25AA}\x{2023}\x{00B7}\x{2043}]\s*/u',
                        '',
                        $first->nodeValue
                    );
                }

                $list->appendChild($item);
                $parent->removeChild($cursor);
                $cursor = $next;
            }

            $parent->insertBefore($list, $cursor);
        }
    }

    /**
     * Give every table its own horizontally scrollable wrapper.
     *
     * A comparison table with three columns has a natural minimum width that a
     * phone cannot give it. Without a wrapper the column simply fell off the
     * edge of the container — the last column of the blog tables was invisible
     * on mobile with no way to reach it. Wrapped, the table scrolls inside its
     * own box while the page around it stays put.
     *
     * `div` is not in the allow-list on purpose, so this runs after the tag
     * filtering rather than before it.
     */
    protected static function wrapTables(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//body//table') ?: [] as $table) {
            $parent = $table->parentNode;

            if (!$parent) {
                continue;
            }

            // Already wrapped — clean() has to stay idempotent.
            if ($parent instanceof DOMElement
                && $parent->nodeName === 'div'
                && str_contains($parent->getAttribute('class'), 'rich-content-table')) {
                continue;
            }

            $wrapper = $table->ownerDocument->createElement('div');
            $wrapper->setAttribute('class', 'rich-content-table');

            $parent->replaceChild($wrapper, $table);
            $wrapper->appendChild($table);
        }
    }

    /**
     * Stop a "/" from landing alone at the start of a wrapped line inside a
     * narrow table cell — "Chronic Allergy /" on one line and "Asthma" on
     * the next, reported on the mobile pricing tables these AI-generated
     * category pages use. A pricing table's cells are the narrowest text on
     * the page (see rich-content.blade.php's table-layout:fixed), so this
     * is scoped to table cells rather than every paragraph on the site.
     *
     * A non-breaking space after the slash (not before) keeps "/ Asthma"
     * together as one unbreakable unit; the browser is still free to wrap
     * the line before "Allergy" as a whole, it just can't split the slash
     * away from the word it introduces.
     */
    protected static function preventOrphanSlashesInTableCells(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//body//table//td//text() | //body//table//th//text()') ?: [] as $text) {
            if (!$text instanceof DOMText || strpos($text->nodeValue, '/') === false) {
                continue;
            }

            // Literal " / " only — "AM/PM", "9/30", "1/2" have no space to
            // begin with and are left alone; there's no wrap risk to fix.
            $value = str_replace(' / ', ' /' . "\u{00A0}", $text->nodeValue);

            if ($value !== $text->nodeValue) {
                $text->nodeValue = $value;
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

    /** Swap a node's tag, keeping its children. Returns the new node, so a caller that needs to set an attribute on it (fixHeadingHierarchy()) can. */
    protected static function rename(DOMNode $node, string $tag): ?DOMElement
    {
        $parent = $node->parentNode;

        if (!$parent) {
            return null;
        }

        $replacement = $node->ownerDocument->createElement($tag);

        while ($node->firstChild) {
            $replacement->appendChild($node->firstChild);
        }

        $parent->replaceChild($replacement, $node);

        return $replacement;
    }

    /**
     * Admins write headings freely — a category's own article can jump
     * straight from h2 to h4, or open with an h4 and never use h2/h3 at all.
     * That is the same "skipped level" problem as the template audit (#?):
     * a screen reader's heading list, and some SEO tools, expect h1 -> h2 ->
     * h3 with nothing skipped, and this content sits right after the page's
     * own h1 (see all.blade.php), so its first heading should read as h2.
     *
     * Standard outline-repair algorithm: walk headings in document order,
     * keep a stack of (original level -> corrected level) for the headings
     * still "open" (an ancestor in the heading outline, not literal DOM
     * nesting — html headings don't nest, levels imply it). A heading whose
     * original level is >= the top of the stack closes that entry (it's a
     * sibling or a return to an outer level); the corrected level is always
     * one below the corrected level of whatever remains open, so two
     * headings that were both h4 under the same h2 both become h3 — not
     * h3 then h4, which a naive "previous+1" pass would produce.
     *
     * Only headings whose level actually changes get touched. Changing the
     * tag changes which font-size/margin-top rule in rich-content.blade.php
     * applies (h2:18px, h3:16px, h4/h5:15px; h3 alone has no top margin) -
     * an inline style pins both back to what the ORIGINAL tag rendered as,
     * so nothing on the page looks different even though the tag is now
     * semantically correct. Must run after stripAttributes() so that style
     * survives (h2-h5 aren't in KEEP_ATTRIBUTES).
     */
    protected static function fixHeadingHierarchy(DOMXPath $xpath): void
    {
        $headings = iterator_to_array($xpath->query('//h2 | //h3 | //h4 | //h5') ?: []);

        if (!$headings) {
            return;
        }

        $fontSizeByOriginalLevel = [2 => '18px', 3 => '16px', 4 => '15px', 5 => '15px'];

        $stackOriginal = [];
        $stackNew = [];

        foreach ($headings as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $originalLevel = (int) substr($node->nodeName, 1);

            while ($stackOriginal && end($stackOriginal) >= $originalLevel) {
                array_pop($stackOriginal);
                array_pop($stackNew);
            }

            $parentNew = $stackNew ? end($stackNew) : 1;
            $newLevel = max(2, min($parentNew + 1, 5));

            $stackOriginal[] = $originalLevel;
            $stackNew[] = $newLevel;

            if ($newLevel === $originalLevel) {
                continue;
            }

            // h2/h4 (not h5) lose their top margin only when they are the
            // very first element in the content - matches the
            // `> h2:first-child, > h4:first-child` rule in rich-content.blade.php.
            $isFirstChild = $node->parentNode
                && $node->parentNode->firstChild === $node;
            $marginTop = ($originalLevel === 3 || ($isFirstChild && in_array($originalLevel, [2, 4], true)))
                ? '0'
                : '18px';

            $style = 'font-size:' . $fontSizeByOriginalLevel[$originalLevel] . ';margin-top:' . $marginTop . ';';
            $existingStyle = $node->getAttribute('style');

            $replacement = static::rename($node, 'h' . $newLevel);
            $replacement?->setAttribute('style', trim($style . $existingStyle));
        }
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

    /**
     * Turns <img src="data:image/...;base64,..."> (pasted or dropped into the
     * editor) into real files under $dir, so the post stores a short URL
     * instead of megabytes of text. Images that fail to decode are dropped.
     */
    public static function storeEmbeddedImages(?string $html, string $dir): string
    {
        $html = (string) $html;
        if (stripos($html, 'data:image') === false) {
            return $html;
        }

        return preg_replace_callback(
            '#(<img\b[^>]*?\bsrc\s*=\s*)(["\'])data:image/(png|jpe?g|gif|webp);base64,([A-Za-z0-9+/=\s]+)\2#i',
            function ($m) use ($dir) {
                $bytes = base64_decode(preg_replace('/\s+/', '', $m[4]), true);
                if ($bytes === false || @getimagesizefromstring($bytes) === false) {
                    return $m[1] . $m[2] . $m[2];
                }
                $ext = strtolower($m[3]) === 'jpeg' ? 'jpg' : strtolower($m[3]);
                $name = $dir . '/' . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
                $path = public_path($name);
                if (!is_dir(dirname($path))) {
                    @mkdir(dirname($path), 0775, true);
                }
                if (@file_put_contents($path, $bytes) === false) {
                    return $m[1] . $m[2] . $m[2];
                }
                return $m[1] . $m[2] . '/' . $name . $m[2];
            },
            $html
        ) ?? $html;
    }
}
