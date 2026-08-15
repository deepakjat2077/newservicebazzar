<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\ServiceKeyword;
use App\Models\Subcategory;
use App\Support\ContentHtml;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Rewrites the stored `description` of categories, subcategories and service
 * keywords through App\Support\ContentHtml.
 *
 * The front end cleans on render too, so this is not required for the pages to
 * look right — it just makes the stored copy match what is shown, which keeps
 * the admin editor honest (open a category and you see the same clean markup
 * the visitor gets) and takes the parse off the render path for good.
 */
class CleanContentHtml extends Command
{
    protected $signature = 'content:clean
        {--dry-run : Show what would change without writing anything}
        {--only= : Limit to one of: categories, subcategories, keywords}';

    protected $description = 'Normalise pasted Word/Docs markup in category and keyword content';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $only = $this->option('only');

        $targets = [
            'categories'    => Category::class,
            'subcategories' => Subcategory::class,
            'keywords'      => ServiceKeyword::class,
        ];

        if ($only !== null && !isset($targets[$only])) {
            $this->error('--only must be one of: ' . implode(', ', array_keys($targets)));

            return self::FAILURE;
        }

        if ($only !== null) {
            $targets = [$only => $targets[$only]];
        }

        if ($dryRun) {
            $this->warn('Dry run — nothing will be saved.');
        }

        $grandTotal = 0;

        foreach ($targets as $label => $class) {
            $grandTotal += $this->process($label, $class, $dryRun);
        }

        $this->newLine();
        $this->info($dryRun
            ? number_format($grandTotal) . ' row(s) would be rewritten.'
            : number_format($grandTotal) . ' row(s) rewritten.');

        return self::SUCCESS;
    }

    /** @param  class-string<Model>  $class */
    protected function process(string $label, string $class, bool $dryRun): int
    {
        $query = $class::query()
            ->whereNotNull('description')
            ->where('description', '<>', '');

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->line("{$label}: nothing to do.");

            return 0;
        }

        $this->line("{$label}: {$total} row(s) with content.");

        $bar = $this->output->createProgressBar($total);
        $changed = 0;
        $bytesBefore = 0;
        $bytesAfter = 0;

        $query->chunkById(100, function ($rows) use (&$changed, &$bytesBefore, &$bytesAfter, $dryRun, $bar) {
            foreach ($rows as $row) {
                $clean = ContentHtml::clean($row->description);

                if ($clean === $row->description) {
                    $bar->advance();
                    continue;
                }

                // Never let a bad parse silently blank someone's page.
                if (trim(strip_tags($clean)) === '' && trim(strip_tags($row->description)) !== '') {
                    $this->newLine();
                    $this->warn("Skipped #{$row->id}: cleaning emptied the content.");
                    $bar->advance();
                    continue;
                }

                $bytesBefore += strlen($row->description);
                $bytesAfter += strlen($clean);
                $changed++;

                if (!$dryRun) {
                    $row->description = $clean;
                    $row->saveQuietly();
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        if ($changed > 0) {
            $saved = $bytesBefore - $bytesAfter;
            $this->line("  {$changed} changed, " . number_format($saved / 1024, 1) . ' KB of markup removed.');
        }

        return $changed;
    }
}
