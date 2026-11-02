<?php

namespace App\Console\Commands;

use App\Support\GoogleIndexing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Hourly: ask Search Console's URL Inspection API whether URLs we sent to the
 * Indexing API are now in Google, and store the answer for the admin Google
 * Indexing page. Order: never checked first, then sent again since the last
 * check, then "not indexed" re-checked after 3 days, "indexed" after 30 days.
 * A URL is first checked a day after sending, so Google has had time to crawl.
 */
class CheckGoogleIndexStatus extends Command
{
    protected $signature = 'seo:google-index-check
                            {--limit=50 : Max URLs this run (Google allows 2,000 inspections a day)}';

    protected $description = 'Check in Search Console whether URLs sent to the Google Indexing API are indexed';

    public function handle(): int
    {
        if (!GoogleIndexing::configured()) {
            $this->error('Key file ' . GoogleIndexing::KEY_FILE . ' is missing or unreadable.');
            return self::FAILURE;
        }

        $rows = DB::table('google_index_submissions')
            ->where('http_status', 200)
            ->where('notified_at', '<=', now()->subDay())
            ->where(function ($q) {
                $q->whereNull('inspected_at')
                    ->orWhereColumn('inspected_at', '<', 'notified_at')
                    ->orWhere(fn ($w) => $w->where('index_verdict', '<>', 'PASS')->where('inspected_at', '<', now()->subDays(3)))
                    ->orWhere(fn ($w) => $w->where('index_verdict', 'PASS')->where('inspected_at', '<', now()->subDays(30)));
            })
            ->orderByRaw('inspected_at IS NOT NULL, inspected_at, notified_at')
            ->limit(max(1, (int) $this->option('limit')))
            ->get(['id', 'url']);

        $ok = 0;
        foreach ($rows as $row) {
            [$status, $error] = GoogleIndexing::inspectAndStore($row->id, $row->url);
            if ($status !== 200) {
                // Disabled API, lost permission or quota: every next call would fail the same way.
                $this->error("HTTP {$status} {$row->url} {$error}");
                break;
            }
            $ok++;
            usleep(300000);
        }

        $this->info("Checked {$ok} of " . $rows->count() . ' URL(s).');
        return self::SUCCESS;
    }
}
