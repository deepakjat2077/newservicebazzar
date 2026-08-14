<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\FileHandler;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
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
            'name'              => 'required|string|max:255|unique:subcategories,name',
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
            'name'              => 'required|string|max:255|unique:subcategories,name,' . $id,
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
    $request->validate([
        'name' => 'required|string|max:255',
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
    $seo = $this->generateSeo($name, optional($category)->name);

    $subcategory = new subcategory();
    $subcategory->name = $name;
    $subcategory->slug = $slug; // 👈 ye add kiya
    $subcategory->category_id = $request->category_id;
    $subcategory->seo_title = $seo['seo_title'];
    $subcategory->seo_keywords = $seo['seo_keywords'];
    $subcategory->seo_description = $seo['seo_description'];
    $subcategory->status = 1;
    $subcategory->save();

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
        'seo_generated' => $seo['generated'],
        'seo_message' => $seo['message'],
    ]);
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
}