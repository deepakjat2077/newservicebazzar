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

            $subcategory = new subcategory();
            $subcategory->category_id = $request->category_id;
            $subcategory->name = $request->name;
            $subcategory->slug = $slug;
            $subcategory->image = $image;
            $subcategory->seo_title = $request->seo_title;
            $subcategory->seo_keywords = $request->seo_keywords;
            $subcategory->seo_description = $request->seo_description;
            // No description: the form no longer has that textarea.
            $subcategory->status = $request->has('status') ? 1 : 0;
            $subcategory->save();

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
        $request->validate([
            'category_id'       => 'required|exists:categories,id',
            'name'              => ['required', 'string', 'max:255', 'unique:subcategories,name,' . $id, new MeaningfulTaxonomyName()],
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'seo_title'         => 'nullable|string|max:255',
            'seo_keywords'      => 'nullable|string|max:500',
            'seo_description'   => 'nullable|string',
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
        return redirect()->route('admin.subcategories.index')->withAlert($alert);

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
        $seoWriter = app(\App\Services\SubcategorySeoWriter::class);
        $aiConfigured = $seoWriter->isConfigured();

        return view('admin.subcategories.bulk-add', [
            'categories' => $categories,
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

        foreach ($names as $name) {
            // Check if already exists
            if (Subcategory::where('category_id', $categoryId)
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->exists()) {
                $skipped++;
                continue;
            }

            try {
                // Generate SEO
                $seo = $seoWriter->generate($name, $category->name);

                // Create subcategory
                $slug = Str::slug($name);
                $originalSlug = $slug;
                $count = 1;

                // Ensure unique slug
                while (Subcategory::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }

                Subcategory::create([
                    'category_id' => $categoryId,
                    'name' => $name,
                    'slug' => $slug,
                    'seo_title' => $seo['seo_title'],
                    'seo_keywords' => $seo['seo_keywords'],
                    'seo_description' => $seo['seo_description'],
                    'status' => 1,
                ]);

                $created++;
            } catch (\Exception $e) {
                $errors[] = "$name: " . $e->getMessage();
            }
        }

        $message = "$created subcategories created";
        if ($skipped > 0) {
            $message .= ", $skipped skipped (already exist)";
        }
        if (!empty($errors)) {
            $message .= ", " . count($errors) . " errors";
        }

        return back()
            ->with('success', $message)
            ->with('errors', $errors);
    }
}