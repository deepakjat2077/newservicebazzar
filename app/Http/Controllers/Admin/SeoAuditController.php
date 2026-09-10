<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SeoAuditor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeoAuditController extends Controller
{
    private const CACHE_KEY = 'seo_audit_rows';

    /** A full scan touches every listing, so hold it for a while. */
    private const CACHE_MINUTES = 30;

    public function index(Request $request)
    {
        $rows = $this->auditedRows();

        $summary = $this->summarise($rows);

        $filtered = $this->applyFilters($rows, $request);

        $perPage = 50;
        $page    = LengthAwarePaginator::resolveCurrentPage();

        $results = new LengthAwarePaginator(
            array_slice($filtered, ($page - 1) * $perPage, $perPage),
            count($filtered),
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return view('admin.seo.audit', [
            'title'       => 'SEO Audit',
            'results'     => $results,
            'summary'     => $summary,
            'issueLabels' => SeoAuditor::issueLabels(),
            'shortLabels' => SeoAuditor::issueShortLabels(),
            'scannedAt'   => Cache::get(self::CACHE_KEY . ':at'),
            'totalScanned' => count($rows),
        ]);
    }

    /**
     * Drop the cached scan so the next page load re-reads the tables.
     */
    public function rescan()
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY . ':at');

        $alert = ['success', 'SEO audit re-scanned'];

        return back()->withAlert($alert);
    }

    private function auditedRows(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_MINUTES), function () {
            Cache::put(self::CACHE_KEY . ':at', now(), now()->addMinutes(self::CACHE_MINUTES));

            return $this->buildRows();
        });
    }

    private function buildRows(): array
    {
        $auditor = new SeoAuditor();
        $rows    = [];

        // Category slugs sit in the `{location}/{slug}` and
        // `listings/{category}/{slug}` routes, so other slugs colliding with
        // them makes a URL ambiguous.
        $categorySlugs = DB::table('categories')
            ->whereNotNull('slug')
            ->pluck('slug')
            ->map(fn ($slug) => trim((string) $slug))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // ---- Listings -----------------------------------------------------
        $listings = DB::table('listings')
            ->select([
                'id', 'name', 'slug', 'meta_title', 'meta_keywords', 'meta_description',
                'details', 'thumb', 'city', 'category_id', 'faq', 'status',
            ])
            ->get();

        $listingDuplicates = $this->duplicateMaps($listings, [
            'meta_title', 'meta_description', 'slug',
        ]);

        foreach ($listings as $listing) {
            $issues = $auditor->auditListing($listing, $listingDuplicates, $categorySlugs);

            $rows[] = $this->row(
                'Listing',
                $listing->id,
                $listing->name,
                route('admin.listing.edit', $listing->id),
                $issues,
                $listing->status
            );
        }

        // ---- Categories ---------------------------------------------------
        $categories = DB::table('categories')
            ->select(['id', 'name', 'slug', 'seo_title', 'seo_keywords', 'seo_description', 'description', 'image', 'status'])
            ->get();

        $categoryDuplicates = $this->duplicateMaps($categories, [
            'seo_title', 'seo_description', 'slug',
        ]);

        foreach ($categories as $category) {
            $rows[] = $this->row(
                'Category',
                $category->id,
                $category->name,
                route('admin.category.index'),
                $auditor->auditTaxonomy($category, $categoryDuplicates),
                $category->status
            );
        }

        // ---- Subcategories -------------------------------------------------
        $subcategories = DB::table('subcategories')
            ->select(['id', 'name', 'slug', 'seo_title', 'seo_keywords', 'seo_description', 'description', 'image', 'status'])
            ->get();

        $subcategoryDuplicates = $this->duplicateMaps($subcategories, [
            'seo_title', 'seo_description', 'slug',
        ]);

        foreach ($subcategories as $subcategory) {
            $rows[] = $this->row(
                'Subcategory',
                $subcategory->id,
                $subcategory->name,
                route('admin.subcategories.index'),
                $auditor->auditTaxonomy($subcategory, $subcategoryDuplicates, checkImage: false),
                $subcategory->status
            );
        }

        // ---- Locations -----------------------------------------------------
        $locations = DB::table('locations')
            ->select(['id', 'name', 'slug', 'image', 'status'])
            ->get();

        $locationDuplicates = $this->duplicateMaps($locations, ['slug']);

        foreach ($locations as $location) {
            $rows[] = $this->row(
                'Location',
                $location->id,
                $location->name,
                route('admin.location.index'),
                $auditor->auditLocation($location, $locationDuplicates, $categorySlugs),
                $location->status
            );
        }

        // Worst pages first — that is what an admin wants to fix.
        usort($rows, function ($a, $b) {
            return [$b['errors'], $b['warnings']] <=> [$a['errors'], $a['warnings']];
        });

        return $rows;
    }

    private function row(string $type, $id, ?string $name, string $editUrl, array $issues, $status): array
    {
        $errors = count(array_filter($issues, fn ($i) => $i['severity'] === 'error'));

        return [
            'type'       => $type,
            'id'         => $id,
            'name'       => $name ?: '(no name)',
            'edit_url'   => $editUrl,
            'status'     => $status,
            'issues'     => $issues,
            'codes'      => array_column($issues, 'code'),
            'errors'     => $errors,
            'warnings'   => count($issues) - $errors,
            'score'      => SeoAuditor::scoreFor($issues),
        ];
    }

    /**
     * value => how many rows share it, per column. Lets the auditor flag
     * duplicate titles/descriptions/slugs without an N+1 of queries.
     */
    private function duplicateMaps($rows, array $columns): array
    {
        $maps = [];

        foreach ($columns as $column) {
            $counts = [];

            foreach ($rows as $row) {
                $value = trim((string) ($row->{$column} ?? ''));

                if ($value === '') {
                    continue;
                }

                $key = mb_strtolower(preg_replace('/\s+/', ' ', $value));

                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }

            $maps[$column] = $counts;
        }

        return $maps;
    }

    private function summarise(array $rows): array
    {
        $issueCounts = [];
        $typeCounts  = [];
        $withErrors  = 0;
        $withIssues  = 0;
        $scoreTotal  = 0;

        foreach ($rows as $row) {
            $scoreTotal += $row['score'];

            if ($row['errors'] > 0) {
                $withErrors++;
            }

            if ($row['issues'] !== []) {
                $withIssues++;
            }

            $typeCounts[$row['type']] = ($typeCounts[$row['type']] ?? 0) + 1;

            foreach (array_unique($row['codes']) as $code) {
                $issueCounts[$code] = ($issueCounts[$code] ?? 0) + 1;
            }
        }

        arsort($issueCounts);

        return [
            'total'       => count($rows),
            'with_errors' => $withErrors,
            'with_issues' => $withIssues,
            'clean'       => count($rows) - $withIssues,
            'avg_score'   => count($rows) ? (int) round($scoreTotal / count($rows)) : 0,
            'issues'      => $issueCounts,
            'types'       => $typeCounts,
        ];
    }

    private function applyFilters(array $rows, Request $request): array
    {
        $type     = $request->query('type');
        $issue    = $request->query('issue');
        $severity = $request->query('severity');
        $search   = trim((string) $request->query('q'));

        return array_values(array_filter($rows, function ($row) use ($type, $issue, $severity, $search) {
            if ($type && $row['type'] !== $type) {
                return false;
            }

            if ($issue && !in_array($issue, $row['codes'], true)) {
                return false;
            }

            if ($severity === 'error' && $row['errors'] === 0) {
                return false;
            }

            if ($severity === 'warning' && $row['warnings'] === 0) {
                return false;
            }

            if ($severity === 'clean' && $row['issues'] !== []) {
                return false;
            }

            if ($search !== '' && !str_contains(mb_strtolower($row['name']), mb_strtolower($search))) {
                return false;
            }

            return true;
        }));
    }
}
