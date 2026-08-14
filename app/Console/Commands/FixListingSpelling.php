<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\SpellFixer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Bulk spelling/grammar pass over listings already in the database.
 *
 * The admin form only corrects what someone is typing right now, so rows
 * added before that existed keep their typos. This walks them.
 *
 * Always dry-run first: the corrections come from a language model, and a
 * bad call on 2,000 rows is far more expensive to undo than to preview.
 */
class FixListingSpelling extends Command
{
    /*
     * Every option definition must stay on one line — a line break inside a
     * {--option : description} block makes Laravel's signature parser miss the
     * option entirely, and it only surfaces at runtime as
     * "The option does not exist".
     */
    protected $signature = 'listings:fix-spelling
        {--dry-run : Show what would change without saving anything}
        {--limit= : Only process this many listings}
        {--category= : Only listings in this category id}
        {--from-id=0 : Resume from this listing id (ids are processed ascending)}
        {--sleep=1 : Seconds to wait between listings, to stay under API rate limits}
        {--touch : Also update updated_at (off by default, so a mass fix does not make every listing look freshly edited)}';

    protected $description = 'Fix spelling/grammar in existing listings using the same corrector as the admin form';

    /** Listing columns worth correcting, in the order they are reported. */
    private const FIELDS = [
        'name',
        'plot_no',
        'building_name',
        'street_name',
        'landmark',
        'contact_person',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'details',
    ];

    public function handle(SpellFixer $fixer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sleep  = max(0, (int) $this->option('sleep'));

        $query = Listing::query()
            ->where('id', '>=', (int) $this->option('from-id'))
            ->orderBy('id');

        if ($this->option('category')) {
            $query->where('category_id', (int) $this->option('category'));
        }

        if ($this->option('limit')) {
            $query->limit((int) $this->option('limit'));
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->warn('No listings matched those filters.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->line($dryRun
            ? "<fg=yellow>DRY RUN</> — nothing will be saved. {$total} listing(s) to scan."
            : "<fg=red>LIVE RUN</> — corrections will be saved. {$total} listing(s) to scan.");

        if (!$dryRun && !$this->option('no-interaction') && !$this->confirm('Continue?', false)) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        $this->newLine();

        $scanned = 0;
        $changedListings = 0;
        $changedFields = 0;
        $failed = 0;
        $lastId = 0;

        foreach ($query->cursor() as $listing) {
            $scanned++;
            $lastId = $listing->id;

            $fields = [];

            foreach (self::FIELDS as $field) {
                $value = (string) ($listing->{$field} ?? '');

                if (trim($value) !== '') {
                    $fields[$field] = $value;
                }
            }

            if ($fields === []) {
                continue;
            }

            $result = $fixer->correct($fields);

            if (!$result['status']) {
                $failed++;
                $this->line("  <fg=red>#{$listing->id}</> failed: " . ($result['message'] ?? 'unknown error'));

                // A failing API will keep failing — stop rather than burn
                // through the whole table logging the same error.
                if ($failed >= 5) {
                    $this->newLine();
                    $this->error('Five consecutive failures — stopping. Resume later with --from-id=' . $lastId);
                    break;
                }

                continue;
            }

            $failed = 0;
            $corrections = $result['corrections'];

            if ($corrections === []) {
                continue;
            }

            $changedListings++;
            $changedFields += count($corrections);

            $this->line("<fg=cyan>#{$listing->id}</> {$listing->name}");

            foreach ($corrections as $field => $change) {
                $this->line("    <fg=gray>{$field}</>");

                foreach ($this->wordChanges($change['from'], $change['to']) as $line) {
                    $this->line('      ' . $line);
                }

                if (!$dryRun) {
                    $listing->{$field} = $change['to'];
                }
            }

            if (!$dryRun) {
                $listing->timestamps = (bool) $this->option('touch');
                $listing->save();

                Log::info('Spelling corrected', [
                    'listing_id' => $listing->id,
                    'fields'     => array_keys($corrections),
                ]);
            }

            if ($sleep > 0) {
                sleep($sleep);
            }
        }

        $this->newLine();
        $this->line(str_repeat('-', 60));
        $this->line("scanned            : {$scanned}");
        $this->line("listings corrected : {$changedListings}");
        $this->line("fields corrected   : {$changedFields}");
        $this->line("last id processed  : {$lastId}");

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry run — nothing was saved. Re-run without --dry-run to apply.');
        }

        return self::SUCCESS;
    }

    /**
     * Word-level diff lines for the console.
     *
     * A 300-word `details` field truncated to 90 characters tells you nothing
     * about what actually changed, which makes the dry run useless for
     * deciding whether to apply. Spelling fixes almost always keep the word
     * count, so matching word-for-word shows exactly the edits; when the
     * counts differ we fall back to a plain before/after preview.
     */
    private function wordChanges(string $before, string $after): array
    {
        $b = preg_split('/\s+/u', trim(preg_replace('/\s+/u', ' ', strip_tags($before))), -1, PREG_SPLIT_NO_EMPTY);
        $a = preg_split('/\s+/u', trim(preg_replace('/\s+/u', ' ', strip_tags($after))), -1, PREG_SPLIT_NO_EMPTY);

        if (count($b) !== count($a)) {
            return [
                '<fg=red>- ' . $this->preview($before) . '</>',
                '<fg=green>+ ' . $this->preview($after) . '</>',
            ];
        }

        $lines = [];

        foreach ($b as $i => $word) {
            if ($word !== $a[$i]) {
                $lines[] = "<fg=red>{$word}</> <fg=gray>→</> <fg=green>{$a[$i]}</>";
            }
        }

        // Word count matched but only whitespace/markup differed.
        return $lines ?: ['<fg=gray>(formatting only)</>'];
    }

    /** One-line, tag-free preview of a value for the console diff. */
    private function preview(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', strip_tags($value)));

        return mb_strlen($value) > 90 ? mb_substr($value, 0, 90) . '…' : $value;
    }
}
