<?php

namespace App\Support\Images;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;

/**
 * Cleans an uploaded SVG before it is written into a public directory.
 *
 * An SVG is a document, not a picture: it can carry <script>, event handlers,
 * and links to other files. Drawn inside an <img> a browser ignores all of
 * that, but the file also sits at its own URL under our own domain — and
 * opened directly there, anything it carries runs as servicebazzar.com. Only
 * admins can upload one, which is exactly why a stolen admin session must not
 * be enough to leave script on the site.
 *
 * What survives: the drawing. Shapes, paths, groups, gradients, text,
 * transforms, fills, strokes and the viewBox.
 */
class SvgSanitizer
{
    /** Elements that exist to run or embed something, not to draw. */
    private const BLOCKED_ELEMENTS = [
        'script', 'foreignobject', 'iframe', 'embed', 'object', 'handler',
        'audio', 'video', 'animate', 'set', 'animatemotion', 'animatetransform',
    ];

    /**
     * Attributes that point somewhere. Kept only when the target is a plain
     * fragment reference (#gradient-1) — never a URL, and never a data: or
     * javascript: payload.
     */
    private const URL_ATTRIBUTES = ['href', 'xlink:href', 'src', 'from', 'to', 'values', 'by'];

    /** An SVG big enough to be a payload rather than an icon. */
    private const MAX_BYTES = 2097152; // 2 MB

    /**
     * @param  string  $path  The uploaded file on disk.
     * @return string  The cleaned SVG markup.
     *
     * @throws Exception when the file is not usable SVG.
     */
    public static function clean(string $path): string
    {
        if (!is_file($path)) {
            throw new Exception('SVG file not found.');
        }

        if (filesize($path) > self::MAX_BYTES) {
            throw new Exception('That SVG is larger than 2 MB.');
        }

        $svg = (string) file_get_contents($path);

        if (trim($svg) === '' || stripos($svg, '<svg') === false) {
            throw new Exception('That file is not an SVG.');
        }

        // A DOCTYPE is the entity-expansion ("billion laughs") route and has no
        // place in an uploaded icon, so the file is refused rather than patched.
        if (preg_match('/<!DOCTYPE/i', $svg)) {
            throw new Exception('That SVG uses a DOCTYPE, which is not allowed.');
        }

        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;

        // LIBXML_NONET: no network fetches while parsing, whatever the file asks for.
        $loaded = @$document->loadXML($svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        if (!$loaded || !$document->documentElement) {
            throw new Exception('That SVG could not be read.');
        }

        if (strtolower($document->documentElement->nodeName) !== 'svg') {
            throw new Exception('That file is not an SVG.');
        }

        self::stripBlockedElements($document);
        self::stripDangerousAttributes($document);

        $clean = $document->saveXML();

        if ($clean === false) {
            throw new Exception('That SVG could not be read.');
        }

        return $clean;
    }

    private static function stripBlockedElements(DOMDocument $document): void
    {
        $xpath = new DOMXPath($document);

        // Walked backwards: removing a node while iterating a live DOMNodeList
        // renumbers the ones after it, so a forward loop skips every second hit.
        $nodes = $xpath->query('//*');

        for ($i = $nodes->length - 1; $i >= 0; $i--) {
            $node = $nodes->item($i);

            if ($node instanceof DOMElement
                && in_array(strtolower($node->localName), self::BLOCKED_ELEMENTS, true)) {
                $node->parentNode?->removeChild($node);
            }
        }

        // Comments can hide markup that a lenient parser later revives.
        $comments = $xpath->query('//comment()');

        for ($i = $comments->length - 1; $i >= 0; $i--) {
            $comment = $comments->item($i);
            $comment->parentNode?->removeChild($comment);
        }
    }

    private static function stripDangerousAttributes(DOMDocument $document): void
    {
        $xpath = new DOMXPath($document);
        $nodes = $xpath->query('//*');

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement || !$node->hasAttributes()) {
                continue;
            }

            $remove = [];

            foreach (iterator_to_array($node->attributes) as $attribute) {
                /** @var DOMAttr $attribute */
                $name = strtolower($attribute->nodeName);
                $value = $attribute->nodeValue ?? '';

                // onload, onclick, onmouseover …
                if (str_starts_with($name, 'on')) {
                    $remove[] = $attribute;
                    continue;
                }

                if ($name === 'style' && preg_match('/expression\s*\(|@import/i', $value)) {
                    $remove[] = $attribute;
                    continue;
                }

                // fill, stroke, filter, mask, clip-path and style all take
                // url(). Pointing one at another site turns a stored icon into
                // a tracking pixel, so only url(#something) in this same file
                // is kept.
                if (preg_match('/url\s*\(\s*[\'"]?(?!#)/i', $value)) {
                    $remove[] = $attribute;
                    continue;
                }

                if (in_array($name, self::URL_ATTRIBUTES, true) && !self::isSafeReference($value)) {
                    $remove[] = $attribute;
                }
            }

            foreach ($remove as $attribute) {
                $node->removeAttributeNode($attribute);
            }
        }
    }

    /**
     * Only an in-document reference such as "#logo-gradient" is kept. Anything
     * that leaves the file — a URL, a data: blob, a javascript: scheme — goes,
     * so a stored icon cannot phone home or carry a second payload.
     */
    private static function isSafeReference(string $value): bool
    {
        $value = trim($value);

        return $value === '' || str_starts_with($value, '#');
    }
}
