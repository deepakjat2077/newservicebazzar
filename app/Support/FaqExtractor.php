<?php

namespace App\Support;

/*
 * Pulls the question/answer pairs out of the rich-text FAQ that admins already
 * write into a category's `description` — the "Frequently Asked Questions"
 * block that renders on every city/category lander. We turn that same visible
 * content into FAQPage JSON-LD so the structured data matches what the reader
 * sees (Google rejects FAQ schema that doesn't appear on the page).
 *
 * The content is Word/Google-Docs paste — heavy nested <div>/<font> soup — so
 * we don't try to walk the DOM tree by sibling. Instead we isolate the FAQ
 * section, then split it on the question headings (<h3>, the pattern every
 * category uses) and take the text between two headings as the answer. Tag
 * stripping flattens the wrapper soup on its own.
 */
class FaqExtractor
{
    /**
     * @return array<int, array{question:string, answer:string}>
     */
    public static function fromHtml(?string $html): array
    {
        if (empty($html)) {
            return [];
        }

        // Isolate everything from the "Frequently Asked" heading onward. If the
        // page has no such heading there is no FAQ to publish.
        if (!preg_match('/frequently\s+asked/i', $html, $m, PREG_OFFSET_CAPTURE)) {
            return [];
        }
        $section = substr($html, $m[0][1]);

        // Question headings. h3 is the house pattern; fall back to h4 if a
        // category happens to use it and has no h3.
        $tag = preg_match('/<h3\b/i', $section) ? 'h3' : (preg_match('/<h4\b/i', $section) ? 'h4' : null);
        if ($tag === null) {
            return [];
        }

        // Split so each chunk after the first begins with a question heading.
        $chunks = preg_split('/<' . $tag . '\b[^>]*>/i', $section);
        if (!is_array($chunks) || count($chunks) < 2) {
            return [];
        }
        array_shift($chunks); // text before the first question (the heading + intro)

        $faqs = [];
        foreach ($chunks as $chunk) {
            // Question = text up to the closing heading tag; answer = the rest,
            // up to the next h2/h1 which would end the FAQ section.
            $parts = preg_split('/<\/' . $tag . '>/i', $chunk, 2);
            if (count($parts) < 2) {
                continue;
            }

            $question = self::clean($parts[0]);

            $answerHtml = preg_split('/<h[12]\b[^>]*>/i', $parts[1], 2)[0];
            $answer = self::clean($answerHtml);

            if ($question === '' || $answer === '') {
                continue;
            }

            // Guard against a stray heading that isn't really a question.
            if (mb_strlen($question) > 300 || mb_strlen($answer) < 3) {
                continue;
            }

            // Not every <h3> in the FAQ block is a question — some are CTA
            // headings ("Your next step"). Keep only real questions so the
            // FAQPage schema stays clean (a '?' or a question-word opener).
            $isQuestion = str_contains($question, '?')
                || preg_match('/^(how|what|why|when|where|who|which|is|are|do|does|did|can|could|should|shall|will|would|may|might|whose|whom)\b/i', $question);
            if (!$isQuestion) {
                continue;
            }

            $faqs[] = [
                'question' => $question,
                'answer'   => mb_substr($answer, 0, 1200),
            ];
        }

        return $faqs;
    }

    private static function clean(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Collapse the whitespace the Word markup leaves behind.
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }
}
