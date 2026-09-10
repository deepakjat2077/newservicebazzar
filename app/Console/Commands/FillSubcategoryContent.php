<?php

namespace App\Console\Commands;

use App\Models\Subcategory;
use App\Services\AiContentGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Backfills the long-form `description` column (what SeoAuditor's
 * content_missing / content_thin checks look at) on subcategories that don't
 * have one, and tops up seo_keywords / seo_description on the same row while
 * it's already asking the model.
 *
 * Written to be interrupted: it only ever touches rows with an empty
 * `description`, so stopping it and running it again simply carries on
 * where it left off (same pattern as FillSubcategorySeo).
 */
class FillSubcategoryContent extends Command
{
    /** Set from --thin in handle(); pendingQuery() and the writer both key off it. */
    protected bool $thinMode = false;

    protected $signature = 'subcategories:fill-content
        {--limit=200 : How many subcategories to process in this run}
        {--batch=10 : How many AI requests to run at once}
        {--dry-run : Show what would be written without saving anything}
        {--sleep=1 : Seconds to pause between batches}
        {--thin : Target rows whose description exists but is under 150 chars, instead of empty ones}';

    protected $description = 'Fill missing long-form description (and any empty seo_keywords/seo_description) on subcategories';

    public function handle(AiContentGenerator $ai): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $batchSize = max(1, min(20, (int) $this->option('batch')));
        $dryRun = (bool) $this->option('dry-run');
        $sleep = max(0, (int) $this->option('sleep'));
        $this->thinMode = (bool) $this->option('thin');

        $pending = $this->pendingQuery()->count();

        $this->info(($this->thinMode ? 'Subcategories with thin content: ' : 'Subcategories still missing content: ') . number_format($pending));

        if ($pending === 0) {
            $this->info('Nothing to do.');

            return self::SUCCESS;
        }

        if (!$ai->isConfigured()) {
            $this->error('No AI key configured (GEMINI_API_KEY) — nothing sensible to write, aborting.');

            return self::FAILURE;
        }

        // Thin-content rows can't be told apart by SQL alone (the 150-char
        // floor is measured after strip_tags), so pull a wider slice and
        // filter precisely in PHP before capping at --limit.
        $rows = $this->thinMode
            ? $this->pendingQuery()->with('category')->limit($limit * 3)->get()
                ->filter(fn ($row) => mb_strlen(trim(html_entity_decode(strip_tags((string) $row->description)))) < 150)
                ->take($limit)
                ->values()
            : $this->pendingQuery()->with('category')->limit($limit)->get();

        $this->line('Processing ' . $rows->count() . ' of them, ' . $batchSize . ' at a time.'
            . ($dryRun ? '  [DRY RUN — nothing will be saved]' : ''));
        $this->newLine();

        $bar = $dryRun ? null : $this->output->createProgressBar($rows->count());
        optional($bar)->start();

        $written = 0;
        $failed = 0;
        $started = microtime(true);

        foreach ($rows->chunk($batchSize) as $chunk) {
            $prompts = [];
            foreach ($chunk as $row) {
                $prompts[$row->id] = $this->prompt($row->name, optional($row->category)->name);
            }

            $answers = $ai->jsonBatch($prompts);

            foreach ($chunk as $row) {
                $answer = $answers[$row->id] ?? ['status' => false, 'data' => []];
                $parsed = $answer['status'] ? $this->parse($answer['data'], $row->name) : null;

                if ($dryRun) {
                    $this->printPreview($row, $parsed, $answer);
                    continue;
                }

                if ($parsed === null) {
                    $failed++;
                    optional($bar)->advance();
                    continue;
                }

                $update = [];
                if ($this->thinMode || trim((string) $row->description) === '') {
                    $update['description'] = $parsed['description'];
                }
                if (trim((string) $row->seo_keywords) === '' && $parsed['seo_keywords'] !== '') {
                    $update['seo_keywords'] = $parsed['seo_keywords'];
                }
                if (trim((string) $row->seo_description) === '' && $parsed['seo_description'] !== '') {
                    $update['seo_description'] = $parsed['seo_description'];
                }

                if ($update) {
                    Subcategory::where('id', $row->id)->update($update);
                    $written++;
                } else {
                    $failed++;
                }

                optional($bar)->advance();
            }

            if ($sleep && !$dryRun) {
                sleep($sleep);
            }
        }

        optional($bar)->finish();
        $this->newLine(2);

        $elapsed = microtime(true) - $started;

        $this->table(['', 'count'], [
            ['rows updated', number_format($written)],
            ['failed / skipped', number_format($failed)],
            ['seconds taken', number_format($elapsed, 1)],
            ['still missing after this run', number_format($this->pendingQuery()->count())],
        ]);

        if (!$dryRun && $this->pendingQuery()->count() > 0) {
            $this->info('Run the command again to continue with the next batch.');
        }

        return self::SUCCESS;
    }

    /**
     * Rows with an empty `description` (default), or — in --thin mode —
     * rows short enough on raw length to be worth the precise PHP re-check
     * in handle(). 300 raw chars comfortably covers every plain-sentence
     * thin row without pulling in the genuinely long ones.
     */
    protected function pendingQuery()
    {
        if ($this->thinMode) {
            return Subcategory::whereNotNull('description')
                ->where('description', '!=', '')
                ->whereRaw('CHAR_LENGTH(description) < 300');
        }

        return Subcategory::where(function ($q) {
            $q->whereNull('description')->orWhere('description', '');
        });
    }

    public function prompt(string $name, ?string $categoryName): string
    {
        $context = $categoryName
            ? "The subcategory is \"{$name}\", which sits under the category \"{$categoryName}\"."
            : "The subcategory is \"{$name}\".";

        return <<<PROMPT
You write on-page content for a page on an Indian local business directory
listing "{$name}" providers.

{$context}

"{$name}" is a type of service or business — not a city or place name.

Return JSON with exactly these keys:
{
  "description": "...",
  "seo_keywords": "...",
  "seo_description": "..."
}

Rules for description:
- 180-260 words
- valid HTML using only <p> and <strong> tags (2-4 paragraphs)
- explains what the service/category covers, what customers should look for
  in a provider, and typical pricing factors — generic to India, no specific
  city, no fake business names, no phone numbers, no links
- natural, factual, no keyword stuffing, no repeating "{$name}" more than 4 times

Rules for seo_keywords:
- 6 to 10 comma-separated keywords, all lowercase, first one is "{$name}" lowercase

Rules for seo_description:
- 120-160 characters, must contain "{$name}"

General: English only, no emojis, no adult or illegal content.
PROMPT;
    }

    /**
     * @return array{description: string, seo_keywords: string, seo_description: string}|null
     */
    public function parse(array $data, string $name): ?array
    {
        $clean = function ($value, $limit) {
            $value = trim((string) $value);
            return mb_substr($value, 0, $limit);
        };

        $description = trim(strip_tags((string) ($data['description'] ?? '')));
        $rawDescription = $clean($data['description'] ?? '', 3000);
        $keywords = $clean($data['seo_keywords'] ?? '', 500);
        $seoDescription = $clean($data['seo_description'] ?? '', 500);

        if (mb_strlen($description) < 150 || $rawDescription === '') {
            return null;
        }

        return [
            'description'      => $rawDescription,
            'seo_keywords'     => $keywords,
            'seo_description'  => $seoDescription,
        ];
    }

    protected function printPreview($row, ?array $parsed, array $answer): void
    {
        $this->line('<fg=cyan>' . $row->name . '</>  <fg=gray>(' . (optional($row->category)->name ?: 'no category') . ')</>');
        if ($parsed === null) {
            $this->line('  FAILED: ' . ($answer['message'] ?? 'unknown'));
        } else {
            $this->line('  description : ' . Str::limit(strip_tags($parsed['description']), 200));
            $this->line('  keywords    : ' . $parsed['seo_keywords']);
            $this->line('  seo_desc    : ' . $parsed['seo_description']);
        }
        $this->newLine();
    }
}
