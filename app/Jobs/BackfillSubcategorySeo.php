<?php

namespace App\Jobs;

use App\Models\Subcategory;
use App\Services\AiContentGenerator;
use App\Services\SubcategorySeoWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Upgrades a batch of just-created subcategories from their instant,
 * deterministic fallback SEO to AI-generated SEO — off the web request.
 *
 * Bulk-adding subcategories used to call the Gemini API once per new name,
 * synchronously, inside the /admin/subcategories/bulk-store request. At ~1.2s
 * a call, a few hundred names ran well past PHP-FPM's request_terminate_timeout
 * (45s) and Cloudflare's ~100s edge timeout, so the whole submit died with a
 * 504 and it was never clear how many had actually been created. Now the
 * controller inserts every row immediately with fallback SEO (no network at
 * all) and hands the AI work to this queued job, which runs the same
 * concurrent batch path the fill-seo command uses.
 *
 * Kept to a small ID set per dispatch so one run comfortably finishes inside
 * the queue worker's timeout (--timeout=90); the controller dispatches several
 * of these for a large paste.
 */
class BackfillSubcategorySeo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int[] */
    protected array $subcategoryIds;

    /** A stuck provider must not hold a worker forever. */
    public int $timeout = 120;

    public int $tries = 2;

    /**
     * @param  int[]  $subcategoryIds
     */
    public function __construct(array $subcategoryIds)
    {
        $this->subcategoryIds = array_values(array_unique(array_map('intval', $subcategoryIds)));
    }

    public function handle(SubcategorySeoWriter $writer, AiContentGenerator $ai): void
    {
        if (empty($this->subcategoryIds) || !$ai->isConfigured()) {
            // Nothing to do, or no AI key — the fallback SEO the controller
            // already wrote stays as the final value.
            return;
        }

        $rows = Subcategory::with('category')
            ->whereIn('id', $this->subcategoryIds)
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $prompts = [];
        foreach ($rows as $row) {
            $prompts[$row->id] = $writer->prompt($row->name, optional($row->category)->name);
        }

        $answers = $ai->jsonBatch($prompts);

        foreach ($rows as $row) {
            $answer = $answers[$row->id] ?? ['status' => false, 'data' => []];

            if (empty($answer['status'])) {
                // Provider gave up on this one — leave the fallback SEO in
                // place rather than blanking it.
                continue;
            }

            $seo = $writer->parse($answer['data'], $row->name, optional($row->category)->name);

            // seo_title stays the deterministic one the writer always returns;
            // only the AI-authored keywords/description are worth overwriting.
            Subcategory::where('id', $row->id)->update([
                'seo_keywords'    => $seo['seo_keywords'],
                'seo_description' => $seo['seo_description'],
            ]);
        }
    }
}
