<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\CategoryPageFaq;
use App\Models\Location;
use App\Services\CategoryFaqWriter;
use Illuminate\Console\Command;

/**
 * Writes "People also search for" Q&As for a category's city pages, as
 * drafts for an admin to approve. Only categories in
 * config('seo.pasf_faq_categories') — a Pet Shop trial for now.
 *
 *   php artisan seo:pasf-faq --category=53 --city=jaipur
 *   php artisan seo:pasf-faq --category=53            (every city with listings, skipping done ones)
 */
class GeneratePasfFaqs extends Command
{
    protected $signature = 'seo:pasf-faq
        {--category=53 : category id}
        {--city= : one city slug; default every city with listings}
        {--force : rewrite cities that already have Q&As (approved ones go back to draft)}
        {--limit=100 : at most this many cities per run}';

    protected $description = 'Write "People also search for" Q&As for a category\'s city pages (drafts for admin approval)';

    public function handle(CategoryFaqWriter $writer): int
    {
        $category = Category::find((int) $this->option('category'));
        if (!$category) {
            $this->error('Category not found.');
            return self::FAILURE;
        }
        if (!CategoryPageFaq::enabledFor($category->id)) {
            $this->error("{$category->name} is not in the trial (config seo.pasf_faq_categories).");
            return self::FAILURE;
        }
        if (!$writer->isConfigured()) {
            $this->error('AI key is not configured.');
            return self::FAILURE;
        }

        $cities = Location::where('status', 1)
            ->whereIn('id', fn ($q) => $q->select('location_id')->from('listings')
                ->where('status', 1)->where('category_id', $category->id))
            ->when($this->option('city'), fn ($q, $slug) => $q->where('slug', $slug))
            ->orderBy('name')
            ->get();

        if (!$this->option('force')) {
            $done = CategoryPageFaq::where('category_id', $category->id)->whereNull('error')->pluck('location_id')->all();
            $cities = $cities->reject(fn ($c) => in_array($c->id, $done));
        }
        $cities = $cities->take(max(1, (int) $this->option('limit')));

        $this->info("{$category->name}: {$cities->count()} cities to write.");

        foreach ($cities as $city) {
            $row = $writer->generate($category, $city);
            $this->line(sprintf('  %-20s %s', $city->name, $row->error ? 'FAILED: ' . $row->error : count($row->faqs ?? []) . ' Q&As (' . $row->keyword_source . '), draft'));
            sleep(1);
        }

        return self::SUCCESS;
    }
}
