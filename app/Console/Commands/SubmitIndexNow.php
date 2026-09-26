<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SubmitIndexNow extends Command
{
    protected $signature = 'seo:indexnow {--dry-run : List the URLs without sending} {--backfill : Submit every live listing not yet submitted} {--days=14 : How far back to look for new listings}';

    protected $description = 'Tell IndexNow search engines (Bing and partners) about new live listings';

    private const STATE = 'indexnow-submitted.json';

    private const BATCH = 5000;

    public function handle(): int
    {
        $key = (string) config('seo.indexnow_key');
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($key === '' || !$host) {
            $this->error('IndexNow key or APP_URL host is missing.');

            return self::FAILURE;
        }

        $done = array_flip(json_decode(Storage::disk('local')->get(self::STATE) ?: '[]', true) ?: []);

        $query = Listing::where('status', 1)->select(['id', 'slug', 'category_id'])->with(['category:id,slug']);

        if (!$this->option('backfill')) {
            $query->where('created_at', '>=', now()->subDays((int) $this->option('days')));
        }

        $pending = [];

        $query->orderBy('id')->chunk(2000, function ($listings) use ($done, &$pending) {
            foreach ($listings as $l) {
                if (isset($done[$l->id]) || empty($l->slug) || empty($l->category?->slug)) {
                    continue;
                }

                $pending[$l->id] = route('listings.details', ['category' => $l->category->slug, 'slug' => $l->slug]);
            }
        });

        $this->info(count($pending) . ' listing URL(s) to submit.');

        if ($this->option('dry-run') || !$pending) {
            foreach (array_slice($pending, 0, 5) as $u) {
                $this->line($u);
            }

            return self::SUCCESS;
        }

        foreach (array_chunk($pending, self::BATCH, true) as $chunk) {
            $response = Http::timeout(30)->post('https://api.indexnow.org/indexnow', [
                'host' => $host,
                'key' => $key,
                'keyLocation' => 'https://' . $host . '/' . $key . '.txt',
                'urlList' => array_values($chunk),
            ]);

            if (!in_array($response->status(), [200, 202], true)) {
                $this->error('IndexNow rejected the batch: HTTP ' . $response->status() . ' ' . substr($response->body(), 0, 200));

                return self::FAILURE;
            }

            foreach (array_keys($chunk) as $id) {
                $done[$id] = true;
            }

            Storage::disk('local')->put(self::STATE, json_encode(array_keys($done)));
            $this->info('Submitted ' . count($chunk) . ' (HTTP ' . $response->status() . ').');
        }

        return self::SUCCESS;
    }
}
