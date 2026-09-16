<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\ListingController;
use App\Models\Listing;
use App\Models\Subcategory;
use App\Services\AiContentGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Writes a listing's AI meta/description/FAQ after it has already been saved.
 *
 * The listing form used to hold the submit back until Gemini answered — two
 * round trips when the first answer failed validation — so the owner watched a
 * spinner for the length of the API call with nothing saved yet, and a slow
 * provider meant a slow "Save". The listing is stored first now (the model's
 * save hooks give it valid deterministic SEO straight away) and this job
 * upgrades that copy a few seconds later.
 *
 * It borrows the controller's own generator rather than restating the prompt,
 * schema, validation and retry that already live there.
 */
class GenerateListingAiContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** A stuck provider must not hold a worker forever. */
    public int $timeout = 120;

    public int $tries = 2;

    public function __construct(protected int $listingId)
    {
    }

    public function handle(AiContentGenerator $ai): void
    {
        if (!$ai->isConfigured()) {
            return;
        }

        $listing = Listing::with('category')->find($this->listingId);

        if (!$listing) {
            return;
        }

        $subcategories = Subcategory::whereIn('id', $listing->subcategory_ids ?? [])
            ->pluck('name')
            ->all();

        $response = app(ListingController::class)->aiGenerate(new Request([
            'name'          => $listing->name,
            'category_name' => optional($listing->category)->name,
            'area'          => $listing->area,
            'city'          => $listing->city,
            'state'         => $listing->state,
            'subcategories' => $subcategories,
        ]));

        $body = $response->getData(true);

        // Generation failed or came back unusable: the deterministic SEO the
        // save hooks wrote is already on the row and stays as the final value.
        if (empty($body['status'])) {
            return;
        }

        $data = $body['data'];

        foreach (['meta_title', 'meta_keywords', 'meta_description', 'details'] as $field) {
            if (trim((string) ($data[$field] ?? '')) !== '') {
                $listing->{$field} = $data[$field];
            }
        }

        if (!empty($data['faqs'])) {
            $listing->faq = [
                'question' => array_column($data['faqs'], 'question'),
                'answer'   => array_column($data['faqs'], 'answer'),
            ];
        }

        $listing->save();
    }
}
