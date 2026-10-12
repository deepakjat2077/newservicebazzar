<?php

namespace App\Support;

use DOMDocument;
use DOMXPath;

/**
 * Table of contents for a blog post: every h2/h3 gets an id made from its own
 * text ("Top 10 Android Phones Like iPhone" -> #Top-10-Android-Phones-Like-iPhone)
 * so a section can be linked to directly, and the list is built from them —
 * h2 as the main entries, the h3s after each h2 numbered under it.
 */
class BlogToc
{
    /** @return array{html: string, items: array<int, array{id: string, text: string, children: array}>} */
    public static function build(string $html): array
    {
        if (stripos($html, '<h2') === false && stripos($html, '<h3') === false) {
            return ['html' => $html, 'items' => []];
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="sb-toc-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $used = [];
        $items = [];
        $current = null;

        foreach ($xpath->query('//h2 | //h3') as $h) {
            $text = trim(preg_replace('/\s+/u', ' ', $h->textContent));
            if ($text === '') {
                continue;
            }

            $id = trim($h->getAttribute('id'));
            if ($id === '') {
                $id = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $text), '-') ?: 'section';
            }
            $base = $id;
            for ($n = 2; isset($used[$id]); $n++) {
                $id = $base . '-' . $n;
            }
            $used[$id] = true;
            $h->setAttribute('id', $id);

            if ($h->nodeName === 'h2') {
                $items[] = ['id' => $id, 'text' => $text, 'children' => []];
                $current = count($items) - 1;
            } elseif ($current !== null) {
                $items[$current]['children'][] = ['id' => $id, 'text' => $text];
            } else {
                $items[] = ['id' => $id, 'text' => $text, 'children' => []];
            }
        }

        $root = $doc->getElementById('sb-toc-root') ?: $xpath->query('//*[@id="sb-toc-root"]')->item(0);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return ['html' => $out, 'items' => $items];
    }
}
