<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\FileHandler;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
use App\Rules\MeaningfulTaxonomyName;
use App\Services\AiContentGenerator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class SubcategoriesController extends Controller
{
    use FileHandler;

    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $subcategories = $query->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('status', 1)->orderBy('name')->get();
        $locations  = Location::where('status', 1)->orderBy('name')->get();

        return view('admin.subcategories.index', compact(
            'subcategories',
            'categories',
            'locations'
        ));
    }

    /**
     * Excel download of every subcategory matching the current filters.
     */
    public function export(Request $request)
    {
        $query = $this->filteredQuery($request)->orderBy('name');

        $location = $request->filled('location_id')
            ? Location::where('id', $request->location_id)->value('name')
            : null;

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SubcategoriesExport($query, $location),
            'subcategories-' . ($location ? Str::slug($location) . '-' : '') . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    /**
     * Search / Category / Location filters, shared by index() and export()
     * so the download always matches the list on screen.
     */
    protected function filteredQuery(Request $request)
    {
        $query = Subcategory::with('category');

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        /*
         * Subcategories have no location column — the link only exists through
         * listings.subcategory_ids, a JSON array, which can't be joined
         * against. So resolve the city's subcategory ids first and filter on
         * those. whereIn([]) on an empty result correctly yields no rows: if
         * no listing in that city tags a subcategory, none of them belong to
         * it.
         */
        if ($request->filled('location_id')) {
            $idsInLocation = Listing::where('location_id', $request->location_id)
                ->whereNotNull('subcategory_ids')
                ->pluck('subcategory_ids')
                ->flatten()
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $query->whereIn('id', $idsInLocation);
        }

        return $query;
    }

    public function createPage()
    {
        $categories = Category::where('status', 1)->get();
        return view('admin.subcategories.create', compact('categories'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'category_id'       => 'required|exists:categories,id',
            'name'              => ['required', 'string', 'max:255', 'unique:subcategories,name', new MeaningfulTaxonomyName()],
            'image'             => 'nullable',
            'seo_title'         => 'nullable|string|max:255',
            'seo_keywords'      => 'nullable|string|max:500',
            'seo_description'   => 'nullable|string',
        ]);

        try {
            $image = null;

            if ($request->hasFile('image')) {
                $imageData = [
                    'file'     => $request->file('image'),
                    'path'     => 'assets/images/subcategory/',
                    'prevFile' => null,
                ];

                $image = $this->uploadFile('image', $imageData);
            }

            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;

            while (Subcategory::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            // The form's SEO fields are optional and this path had no
            // fallback at all — a subcategory created with them left blank
            // saved that way permanently, which is exactly what kept
            // showing up as "Title short" / "No content" / "Keywords
            // missing" in the SEO audit. Fill in the same deterministic
            // defaults quick-add and bulk-add already use whenever a field
            // comes in empty; anything the admin actually typed is kept.
            $category = Category::find($request->category_id);
            $seo = app(\App\Services\SubcategorySeoWriter::class)->fallback($request->name, optional($category)->name);

            $subcategory = new subcategory();
            $subcategory->category_id = $request->category_id;
            $subcategory->name = $request->name;
            $subcategory->slug = $slug;
            $subcategory->image = $image;
            $subcategory->seo_title = $request->filled('seo_title') ? $request->seo_title : $seo['seo_title'];
            $subcategory->seo_keywords = $request->filled('seo_keywords') ? $request->seo_keywords : $seo['seo_keywords'];
            $subcategory->seo_description = $request->filled('seo_description') ? $request->seo_description : $seo['seo_description'];
            // No description field on the form — every subcategory created
            // here used to save with content_missing guaranteed. A short
            // deterministic paragraph beats none; subcategories:fill-content
            // (AI-written, richer) still upgrades it later since it only
            // ever touches rows under its own length floor.
            $subcategory->description = "<p><strong>{$request->name}</strong> is a category of "
                . (optional($category)->name ?: 'service') . " providers on ServiceBazzar. "
                . "Compare verified listings, check ratings and contact the right provider directly for pricing and availability.</p>";
            $subcategory->status = $request->has('status') ? 1 : 0;
            $subcategory->save();

            // Unlike quick-add, this form lets the admin type seo_title /
            // seo_keywords / seo_description directly — only the fields
            // that came in blank were replaced above, and the async AI
            // enrichment quick-add uses is deliberately NOT run here, since
            // it would overwrite that manual input with a generated answer
            // a few seconds later.

            $alert = ['success', 'Subcategory created successfully'];
            return redirect()->route('admin.subcategories.index')->withAlert($alert);

        } catch (\Exception $e) {
            $alert = ['danger', $e->getMessage()];
            return back()->withInput()->withAlert($alert);
        }
    }

    public function update($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        $categories = Category::where('status', 1)->get();

        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function edit(Request $request, $id)
    {
        // Renamed to a name another subcategory of the SAME category already has
        // (e.g. "1. Pet Shops Near Me in Varanasi" -> "Pet Shops Near Me in
        // Varanasi"): merge this one into that one instead of refusing to save.
        $sameName = Subcategory::where('id', '!=', $id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim((string) $request->name))])
            ->first();
        if ($sameName && (int) $sameName->category_id === (int) $request->category_id) {
            return $this->mergeInto(Subcategory::findOrFail($id), $sameName);
        }

        $request->validate([
            'category_id'       => 'required|exists:categories,id',
            'name'              => ['required', 'string', 'max:255', 'unique:subcategories,name,' . $id, new MeaningfulTaxonomyName()],
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'seo_title'         => 'nullable|string|max:255',
            'seo_keywords'      => 'nullable|string|max:500',
            'seo_description'   => 'nullable|string',
        ], [
            'name.unique' => $sameName
                ? 'Another category already has a subcategory named "' . $sameName->name . '" (ID ' . $sameName->id . ', ' . optional($sameName->category)->name . '). Choose a different name.'
                : 'The name has already been taken.',
        ]);

        $subcategory = Subcategory::findOrFail($id);
        $image = $subcategory->image;

        if ($request->hasFile('image')) {
            try {
                $imageData = [
                    'file'     => $request->file('image'),
                    'path'     => 'assets/images/subcategory/',
                    'prevFile' => $subcategory->image,
                ];

                $image = $this->uploadFile('image', $imageData);
            } catch (\Exception $e) {
                $alert = ['danger', 'Something wrong in subcategory image upload'];
                return back()->withAlert($alert);
            }
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;

        while (Subcategory::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $subcategory->category_id = $request->category_id;
        $subcategory->name = $request->name;
        $subcategory->slug = $slug;
        $subcategory->image = $image;
        $subcategory->seo_title = $request->seo_title;
        $subcategory->seo_keywords = $request->seo_keywords;
        $subcategory->seo_description = $request->seo_description;
        // description is deliberately NOT assigned. The textarea is gone from
        // the form, so $request->description is always null here — writing it
        // would wipe the stored content of every subcategory that gets edited,
        // and that content still renders on the frontend.
        $subcategory->status = $request->has('status') ? 1 : 0;
        $subcategory->save();

        $alert = ['success', 'Subcategory updated successfully'];
      return redirect()->route('admin.subcategories.index')->withAlert($alert);
    }
    /**
     * Fold $dup into $target: its listings move to $target, its public URL
     * 301s to $target's, and the duplicate row is removed (logged first).
     */
    private function mergeInto(Subcategory $dup, Subcategory $target)
    {
        $moved = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($dup, $target, &$moved) {
            Listing::where(function ($q) use ($dup) {
                $q->whereJsonContains('subcategory_ids', (string) $dup->id)
                  ->orWhereJsonContains('subcategory_ids', $dup->id);
            })->select(['id', 'subcategory_ids'])->chunkById(200, function ($listings) use ($dup, $target, &$moved) {
                foreach ($listings as $listing) {
                    $ids = collect($listing->subcategory_ids ?? [])
                        ->map(fn ($v) => (string) $v)
                        ->reject(fn ($v) => $v === (string) $dup->id)
                        ->push((string) $target->id)
                        ->unique()
                        ->values()
                        ->all();
                    \Illuminate\Support\Facades\DB::table('listings')->where('id', $listing->id)
                        ->update(['subcategory_ids' => json_encode($ids)]);
                    $moved++;
                }
            });

            if ($dup->slug && $target->slug) {
                \App\Models\UrlRedirect::remember('listings/subcategory/' . $dup->slug, 'listings/subcategory/' . $target->slug);
            }

            \Illuminate\Support\Facades\Log::info('Subcategory merged on rename', [
                'from' => $dup->only(['id', 'name', 'slug', 'category_id', 'seo_title', 'seo_keywords', 'seo_description', 'image']),
                'into' => $target->id,
                'listings_moved' => $moved,
            ]);

            $dup->delete();
        });

        $alert = ['success', 'Saved. "' . $target->name . '" already existed (ID ' . $target->id . '), so this duplicate was merged into it'
            . ($moved ? ' and its ' . $moved . ' listing(s) moved there.' : '.')];

        return redirect()->route('admin.subcategories.update.page', $target->id)->withAlert($alert);
    }

    public function delete($id)
{
    try {
        $subcategory = Subcategory::findOrFail($id);

        // old image delete
        if (!empty($subcategory->image)) {
            $imagePath = public_path('assets/images/subcategory/' . $subcategory->image);

            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $subcategory->delete();

        $alert = ['success', 'Subcategory deleted successfully'];
        $params = array_filter([
            'page'        => request('page', 1),
            'q'           => request('q'),
            'category_id' => request('category_id'),
            'location_id' => request('location_id'),
        ]);
        return redirect()->route('admin.subcategories.index', $params)->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', $e->getMessage()];
        return back()->withAlert($alert);
    }
}


public function quickStore(Request $request)
{
    /*
     * Same name rule as the admin create/edit forms. Quick-add is reached from
     * the listing form, where a subcategory gets typed in a hurry mid-listing —
     * so it is if anything the likelier source of placeholder names, and it was
     * the one path with no check at all.
     */
    $request->validate([
        'name' => ['required', 'string', 'max:255', new MeaningfulTaxonomyName()],
        'category_id' => 'required|exists:categories,id',
    ]);

    $name = trim($request->name);

    $existing = Subcategory::where('category_id', $request->category_id)
        ->whereRaw('LOWER(name) = ?', [strtolower($name)])
        ->first();

    if ($existing) {
        return response()->json([
            'status' => true,
            'message' => 'Sub category already exists',
            'data' => [
                'id' => $existing->id,
                'name' => $existing->name,
                'category_id' => $existing->category_id,
            ]
        ]);
    }

    $slug = Str::slug($name);

    $originalSlug = $slug;
    $count = 1;

    while (Subcategory::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $count++;
    }

    $category = Category::find($request->category_id);

    // Quick-add used to save only the name, leaving the SEO columns empty on
    // every subcategory created from the listing form. Fill them from the
    // same AI the listing form's auto-content uses.
    //
    // The AI call is deliberately NOT made before the insert: with retries it
    // can hold the request for the best part of a minute, which is longer than
    // the max_execution_time the app runs under, so the admin lost the whole
    // subcategory to a 500 instead of merely losing its generated text. The row
    // is written with the deterministic fallback first and the response goes
    // out; the AI then upgrades the row once the browser already has its answer.
    $writer = app(\App\Services\SubcategorySeoWriter::class);
    $seo = $writer->fallback($name, optional($category)->name);

    $subcategory = new subcategory();
    $subcategory->name = $name;
    $subcategory->slug = $slug; // 👈 ye add kiya
    $subcategory->category_id = $request->category_id;
    $subcategory->seo_title = $seo['seo_title'];
    $subcategory->seo_keywords = $seo['seo_keywords'];
    $subcategory->seo_description = $seo['seo_description'];
    // Same reasoning as create()/bulkStore(): this path has no description
    // field, so it always saved content_missing without this.
    $subcategory->description = "<p><strong>{$name}</strong> is a category of " . (optional($category)->name ?: 'service')
        . " providers on ServiceBazzar. Compare verified listings, check ratings and contact the right provider directly for pricing and availability.</p>";
    $subcategory->status = 1;
    $subcategory->save();

    $this->enrichSeoAfterResponse($subcategory->id, $name, optional($category)->name);

    return response()->json([
        'status' => true,
        'message' => 'Sub category added successfully',
        'data' => [
            'id' => $subcategory->id,
            'name' => $subcategory->name,
            'slug' => $subcategory->slug, // 👈 response me bhi de diya
            'category_id' => $subcategory->category_id,
            'seo_title' => $subcategory->seo_title,
            'seo_keywords' => $subcategory->seo_keywords,
            'seo_description' => $subcategory->seo_description,
        ],
        'seo_generated' => false,
        'seo_message' => 'Sub category saved; SEO text is being written in the background.',
    ]);
}

    /**
     * Rewrite the row's SEO columns from the AI once the response has been
     * flushed to the browser.
     *
     * Runs in this same process rather than on the queue, because the app has
     * no worker running and a queued job would simply never be picked up. If
     * the process is killed before it finishes, the row keeps its fallback text
     * and `subcategories:fill-seo` picks it up later — the one thing that must
     * not happen is the admin's subcategory going missing, and by this point it
     * is already committed.
     */
    protected function enrichSeoAfterResponse(int $id, string $name, ?string $categoryName): void
    {
        app()->terminating(function () use ($id, $name, $categoryName) {
            try {
                $seo = $this->generateSeo($name, $categoryName);

                if (!$seo['generated']) {
                    return;
                }

                Subcategory::where('id', $id)->update([
                    'seo_title'       => $seo['seo_title'],
                    'seo_keywords'    => $seo['seo_keywords'],
                    'seo_description' => $seo['seo_description'],
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Subcategory SEO enrichment failed', [
                    'subcategory_id' => $id,
                    'error'          => $exception->getMessage(),
                ]);
            }
        });
    }

    /**
     * SEO for a subcategory, delegated to SubcategorySeoWriter so the
     * listing form's quick-add and the `subcategories:fill-seo` backfill
     * command can never drift apart.
     */
    protected function generateSeo(string $name, ?string $categoryName): array
    {
        return app(\App\Services\SubcategorySeoWriter::class)->generate($name, $categoryName);
    }

    /**
     * Bulk add page — AI-powered form to add multiple subcategories at once
     */
    public function bulkAddPage()
    {
        $categories = Category::where('status', 1)->orderBy('name')->get();
        $locations = Location::where('status', 1)->orderBy('name')->get();
        $seoWriter = app(\App\Services\SubcategorySeoWriter::class);
        $aiConfigured = $seoWriter->isConfigured();

        return view('admin.subcategories.bulk-add', [
            'categories' => $categories,
            'locations' => $locations,
            'aiConfigured' => $aiConfigured,
        ]);
    }

    /**
     * Bulk store — process list of subcategory names with AI-generated SEO
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'names' => 'required|string',
        ]);

        $categoryId = $validated['category_id'];
        $category = Category::findOrFail($categoryId);

        // Parse the names (one per line)
        $names = collect(explode("\n", $validated['names']))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return back()->withError('No subcategory names provided.');
        }

        $seoWriter = app(\App\Services\SubcategorySeoWriter::class);
        $created = 0;
        $skipped = 0;
        $errors = [];
        $createdIds = [];

        foreach ($names as $name) {
            // Check if already exists
            if (Subcategory::where('category_id', $categoryId)
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->exists()) {
                $skipped++;
                continue;
            }

            try {
                // Fallback SEO only — deterministic, no network call. The AI
                // version is generated afterwards by a queued job (see below).
                // This is what stopped the whole submit from 504-ing: it used
                // to call the Gemini API once per new name, synchronously, so a
                // paste of a few hundred names ran ~1.2s x N well past PHP-FPM's
                // 45s terminate timeout and Cloudflare's ~100s edge timeout.
                $seo = $seoWriter->fallback($name, $category->name);

                // Create subcategory
                $slug = Str::slug($name);
                $originalSlug = $slug;
                $count = 1;

                // Ensure unique slug
                while (Subcategory::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }

                $subcategory = Subcategory::create([
                    'category_id' => $categoryId,
                    'name' => $name,
                    'slug' => $slug,
                    'seo_title' => $seo['seo_title'],
                    'seo_keywords' => $seo['seo_keywords'],
                    'seo_description' => $seo['seo_description'],
                    // This path has no description field either — same
                    // deterministic paragraph create() now writes, so a
                    // bulk-added batch doesn't reopen the SEO audit's
                    // "No content" bucket the day it's created.
                    'description' => "<p><strong>{$name}</strong> is a category of {$category->name} providers on ServiceBazzar. "
                        . "Compare verified listings, check ratings and contact the right provider directly for pricing and availability.</p>",
                    'status' => 1,
                ]);

                $createdIds[] = $subcategory->id;
                $created++;
            } catch (\Exception $e) {
                $errors[] = "$name: " . $e->getMessage();
            }
        }

        // Hand the AI SEO off to the queue, in small batches so each job
        // finishes inside the worker's timeout. The rows already have valid
        // fallback SEO, so nothing is broken while these run — the AI just
        // upgrades the keywords/description shortly after.
        if (!empty($createdIds) && $seoWriter->isConfigured()) {
            foreach (array_chunk($createdIds, 25) as $chunk) {
                \App\Jobs\BackfillSubcategorySeo::dispatch($chunk);
            }
        }

        $message = "$created subcategories created";
        if ($skipped > 0) {
            $message .= ", $skipped skipped (already exist)";
        }
        if (!empty($createdIds) && $seoWriter->isConfigured()) {
            $message .= ". AI SEO is being generated in the background and will appear in a few minutes.";
        }
        if (!empty($errors)) {
            $message .= " " . count($errors) . " errors.";
        }

        return back()
            ->with('success', $message)
            ->with('errors', $errors);
    }

    /**
     * Get subcategories by category ID (for AJAX)
     */
    public function byCategory($categoryId)
    {
        // Get unique subcategories by name, taking the first ID for each unique name
        $subcategories = Subcategory::where('category_id', $categoryId)
            ->where('status', 1)
            ->select('id', 'name')
            ->distinct('name')
            ->orderBy('name')
            ->get()
            ->unique('name')  // Additional deduplication in case distinct() doesn't work as expected
            ->values();

        return response()->json($subcategories);
    }
}