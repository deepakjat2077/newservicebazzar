<?php

namespace App\Console\Commands;

use App\Models\Subcategory;
use App\Services\AiContentGenerator;
use App\Services\SubcategorySeoWriter;
use Illuminate\Console\Command;

/**
 * Backfills seo_title / seo_keywords / seo_description on subcategories that
 * are missing them.
 *
 * Written to be interrupted: it only ever touches rows with an empty column,
 * so stopping it and running it again simply carries on where it left off.
 */
class FillSubcategorySeo extends Command
{
    protected $signature = 'subcategories:fill-seo
        {--limit=200 : How many subcategories to process in this run}
        {--batch=10 : How many AI requests to run at once}
        {--dry-run : Show what would be written without saving anything}
        {--sleep=1 : Seconds to pause between batches}';

    protected $description = 'Fill missing SEO title, keywords and description on subcategories';

    public function handle(SubcategorySeoWriter $writer, AiContentGenerator $ai): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $batchSize = max(1, min(20, (int) $this->option('batch')));
        $dryRun = (bool) $this->option('dry-run');
        $sleep = max(0, (int) $this->option('sleep'));

        $pending = $this->pendingQuery()->count();

        $this->info('Subcategories still missing SEO: ' . number_format($pending));

        if ($pending === 0) {
            $this->info('Nothing to do.');

            return self::SUCCESS;
        }

        if (!$ai->isConfigured()) {
            $this->warn('No AI key configured (GEMINI_API_KEY) — the built-in default text will be used instead.');
        }

        $rows = $this->pendingQuery()->with('category')->limit($limit)->get();

        $this->line('Processing ' . $rows->count() . ' of them, ' . $batchSize . ' at a time.'
            . ($dryRun ? '  [DRY RUN — nothing will be saved]' : ''));
        $this->newLine();

        $bar = $dryRun ? null : $this->output->createProgressBar($rows->count());
        optional($bar)->start();

        $written = 0;
        $byAi = 0;
        $byFallback = 0;
        $started = microtime(true);

        foreach ($rows->chunk($batchSize) as $chunk) {
            $results = $this->resultsFor($chunk, $writer, $ai);

            foreach ($chunk as $row) {
                $seo = $results[$row->id];

                if ($seo['generated']) {
                    $byAi++;
                } else {
                    $byFallback++;
                }

                if ($dryRun) {
                    $this->printPreview($row, $seo);
                    continue;
                }

                // Only fill what is actually empty — a subcategory someone
                // wrote by hand must not be overwritten.
                $update = [];

                foreach (['seo_title', 'seo_keywords', 'seo_description'] as $column) {
                    if (trim((string) $row->{$column}) === '') {
                        $update[$column] = $seo[$column];
                    }
                }

                if ($update) {
                    Subcategory::where('id', $row->id)->update($update);
                    $written++;
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
            ['written by AI', number_format($byAi)],
            ['written from default text', number_format($byFallback)],
            ['seconds taken', number_format($elapsed, 1)],
            ['still missing after this run', number_format($this->pendingQuery()->count())],
        ]);

        if (!$dryRun && $this->pendingQuery()->count() > 0) {
            $this->info('Run the command again to continue with the next batch.');
        }

        return self::SUCCESS;
    }

    /** Rows with at least one of the three SEO columns empty. */
    protected function pendingQuery()
    {
        return Subcategory::where(function ($q) {
            foreach (['seo_title', 'seo_keywords', 'seo_description'] as $column) {
                $q->orWhereNull($column)->orWhere($column, '');
            }
        });
    }

    /**
     * SEO for a whole chunk, in one concurrent round trip.
     *
     * @return array<int, array>  keyed by subcategory id
     */
    protected function resultsFor($chunk, SubcategorySeoWriter $writer, AiContentGenerator $ai): array
    {
        $out = [];

        if (!$ai->isConfigured()) {
            foreach ($chunk as $row) {
                $out[$row->id] = $writer->fallback($row->name, optional($row->category)->name)
                    + ['generated' => false, 'message' => 'AI not configured'];
            }

            return $out;
        }

        $prompts = [];

        foreach ($chunk as $row) {
            $prompts[$row->id] = $writer->prompt($row->name, optional($row->category)->name);
        }

        $answers = $ai->jsonBatch($prompts);

        foreach ($chunk as $row) {
            $answer = $answers[$row->id] ?? ['status' => false, 'data' => [], 'message' => 'no answer'];

            $out[$row->id] = $answer['status']
                ? $writer->parse($answer['data'], $row->name, optional($row->category)->name)
                : $writer->fallback($row->name, optional($row->category)->name)
                    + ['generated' => false, 'message' => $answer['message']];
        }

        return $out;
    }

    protected function printPreview($row, array $seo): void
    {
        $this->line('<fg=cyan>' . $row->name . '</>  <fg=gray>(' . (optional($row->category)->name ?: 'no category') . ')</>');
        $this->line('  title       : ' . $seo['seo_title']);
        $this->line('  keywords    : ' . $seo['seo_keywords']);
        $this->line('  description : ' . $seo['seo_description']);
        $this->line('  source      : ' . ($seo['generated'] ? 'AI' : 'default text'));
        $this->newLine();
    }
}
