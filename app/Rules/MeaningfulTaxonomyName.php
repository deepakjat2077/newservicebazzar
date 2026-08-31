<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A category/subcategory name that can carry a public page.
 *
 * Every subcategory becomes a crawlable URL, and the name field only ever
 * validated `max:255` — so placeholder saves like "ddd" and "ddddd" went
 * straight to production as live, indexable, empty pages. An SEO audit picked
 * them up as a thin-content and crawl-quality signal.
 *
 * The checks here are deliberately narrow. A false positive blocks an admin
 * from saving a name that is perfectly real, which is worse than letting one
 * more piece of junk through — the junk is visible in the admin list and can be
 * deleted, whereas a rule nobody can get past just gets worked around. So this
 * only rejects patterns that no genuine service name has: too short to be a
 * word, no letters at all, one letter held down on the keyboard, or a literal
 * placeholder word.
 *
 * What it deliberately does NOT try to catch is invented-but-plausible strings
 * such as "cdb x" — separating those from real trade abbreviations needs a
 * dictionary, and the naive heuristics for it (no vowels, short tokens) reject
 * real names like "CCTV" and "Vitamin C". Those stay a human review job.
 */
class MeaningfulTaxonomyName implements ValidationRule
{
    /**
     * Words that are only ever typed while testing something.
     *
     * Matched against the whole trimmed name, never as a substring, so real
     * names that merely contain one of them ("New Born Care", "Sample
     * Collection At Home") are unaffected.
     */
    private const PLACEHOLDER_WORDS = [
        'test', 'testing', 'test1', 'test123',
        'abc', 'abcd', 'xyz', 'asdf', 'qwerty',
        'demo', 'sample', 'dummy', 'temp', 'tmp',
        'new', 'none', 'na', 'null', 'undefined',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = trim((string) $value);

        if (mb_strlen($name) < 3) {
            $fail('The :attribute must be at least 3 characters — it becomes a public page.');

            return;
        }

        // "123", "---", "&&&" — nothing for a page to be about.
        if (preg_match_all('/\p{L}/u', $name) < 2) {
            $fail('The :attribute must contain at least two letters.');

            return;
        }

        /*
         * One key held down: "ddd", "DDDD", even "d d d". Compared on the
         * letters alone so spacing and punctuation cannot disguise it.
         */
        $letters = mb_strtolower(preg_replace('/[^\p{L}]/u', '', $name));

        if (count(array_unique(preg_split('//u', $letters, -1, PREG_SPLIT_NO_EMPTY))) === 1) {
            $fail('The :attribute does not look like a real name.');

            return;
        }

        // Same key held down inside a longer string: "ddddd services".
        if (preg_match('/(\p{L})\1{3,}/u', $name)) {
            $fail('The :attribute does not look like a real name.');

            return;
        }

        if (in_array(mb_strtolower($name), self::PLACEHOLDER_WORDS, true)) {
            $fail('The :attribute looks like a placeholder. Please enter the real name.');
        }
    }
}
