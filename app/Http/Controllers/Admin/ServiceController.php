<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Str;


class ServiceController extends Controller
{
    
     protected $setting;
    protected $theme;

    public function __construct()
    {
        try {
            $this->setting = \App\Models\Setting::first();
            $this->theme = 'themes.' . $this->setting->theme . '.';
        } catch (\Exception $e) {
            $this->setting = null;
            $this->theme = 'themes.basic.'; // fallback
        }
    }
    
    
       public function index()
{
    $services = Service::where('status', 1)->latest()->get();
    $title = 'Services';

    return view($this->theme . 'services', compact('services', 'title'));
}
  public function adminindex()
{
    $title = 'Services List';
    $services = Service::latest()->paginate(10);

    return view('admin.services.index', compact('title', 'services'));
}

   public function create()
{
    $title = 'Add Service';
    return view('admin.services.create', compact('title'));
}


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug',
            'menu_title' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',


            


            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',

            'show_in_menu' => 'nullable|in:0,1',
            'status' => 'nullable|in:0,1',

            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'intro_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'trusted_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'seo_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'why_choose_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'partner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cta_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cards.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $service = new Service();

        $service->title = $request->title;
        $service->slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);
        $service->menu_title = $request->menu_title;
        $service->short_description = $request->short_description;
        $service->description = $request->description;

        $service->hero_title = $request->hero_title;
        $service->intro_content = $request->intro_content;

        $service->trusted_title = $request->trusted_title;
        $service->trusted_description = $request->trusted_description;

        $service->services_section_title = $request->services_section_title;

        $service->seo_title = $request->seo_title;
        $service->seo_description = $request->seo_description;

        $service->why_choose_title = $request->why_choose_title;

        $service->process_title = $request->process_title;

        $service->partner_title = $request->partner_title;
        $service->partner_points = $request->partner_points;

        $service->faq_title = $request->faq_title;

        $service->cta_title = $request->cta_title;
        $service->cta_description = $request->cta_description;
        $service->cta_button_text = $request->cta_button_text;
        $service->cta_button_link = $request->cta_button_link;

        $service->meta_title = $request->meta_title;
        $service->meta_keywords = $request->meta_keywords;
        $service->meta_description = $request->meta_description;

        $service->show_in_menu = $request->show_in_menu ?? 1;
        $service->status = $request->status ?? 1;

        // single image upload helper
        $uploadPath = 'uploads/services';

        if ($request->hasFile('hero_image')) {
            $file = $request->file('hero_image');
            $name = time() . '_hero_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($uploadPath), $name);
            $service->hero_image = $uploadPath . '/' . $name;
        }

        if ($request->hasFile('intro_image')) {
            $file = $request->file('intro_image');
            $name = time() . '_intro_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($uploadPath), $name);
            $service->intro_image = $uploadPath . '/' . $name;
        }

        if ($request->hasFile('trusted_image')) {
            $file = $request->file('trusted_image');
            $name = time() . '_trusted_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($uploadPath), $name);
            $service->trusted_image = $uploadPath . '/' . $name;
        }

        if ($request->hasFile('seo_image')) {
            $file = $request->file('seo_image');
            $name = time() . '_seo_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($uploadPath), $name);
            $service->seo_image = $uploadPath . '/' . $name;
        }

        if ($request->hasFile('why_choose_image')) {
            $file = $request->file('why_choose_image');
            $name = time() . '_why_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($uploadPath), $name);
            $service->why_choose_image = $uploadPath . '/' . $name;
        }

        if ($request->hasFile('partner_image')) {
            $file = $request->file('partner_image');
            $name = time() . '_partner_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($uploadPath), $name);
            $service->partner_image = $uploadPath . '/' . $name;
        }

        if ($request->hasFile('cta_image')) {
            $file = $request->file('cta_image');
            $name = time() . '_cta_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($uploadPath), $name);
            $service->cta_image = $uploadPath . '/' . $name;
        }

        // menu items
        $menuItems = [];
        if ($request->has('menu_items') && is_array($request->menu_items)) {
            foreach ($request->menu_items as $item) {
                if (!empty($item['title'])) {
                    $menuItems[] = [
                        'title' => $item['title'],
                    ];
                }
            }
        }
        $service->menu_items = $menuItems;

        // cards with image
        $cards = [];
        if ($request->has('cards') && is_array($request->cards)) {
            foreach ($request->cards as $index => $card) {
                if (
                    empty($card['title']) &&
                    empty($card['description']) &&
                    !$request->hasFile("cards.$index.image")
                ) {
                    continue;
                }

                $cardImage = null;
                if ($request->hasFile("cards.$index.image")) {
                    $file = $request->file("cards.$index.image");
                    $name = time() . '_card_' . $index . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path($uploadPath), $name);
                    $cardImage = $uploadPath . '/' . $name;
                }

                $cards[] = [
                    'title' => $card['title'] ?? '',
                    'description' => $card['description'] ?? '',
                    'image' => $cardImage,
                ];
            }
        }
        $service->cards = $cards;

        // features
        $features = [];
        if ($request->has('features') && is_array($request->features)) {
            foreach ($request->features as $feature) {
                if (empty($feature['title']) && empty($feature['description'])) {
                    continue;
                }

                $features[] = [
                    'title' => $feature['title'] ?? '',
                    'description' => $feature['description'] ?? '',
                ];
            }
        }
        $service->features = $features;

        // process steps
        $processSteps = [];
        if ($request->has('process_steps') && is_array($request->process_steps)) {
            foreach ($request->process_steps as $step) {
                if (empty($step['number']) && empty($step['title'])) {
                    continue;
                }

                $processSteps[] = [
                    'number' => $step['number'] ?? '',
                    'title' => $step['title'] ?? '',
                ];
            }
        }
        $service->process_steps = $processSteps;

        // faqs
        $faqs = [];
        if ($request->has('faqs') && is_array($request->faqs)) {
            foreach ($request->faqs as $faq) {
                if (empty($faq['question']) && empty($faq['answer'])) {
                    continue;
                }

                $faqs[] = [
                    'question' => $faq['question'] ?? '',
                    'answer' => $faq['answer'] ?? '',
                ];
            }
        }
        $service->faqs = $faqs;

        $service->save();

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }
    
    public function edit($id)
{
    $service = Service::findOrFail($id);
    $title = 'Edit Service';

    return view('admin.services.edit', compact('service', 'title'));
}

public function update(Request $request, $id)
{
    $service = Service::findOrFail($id);

    $request->validate([
        'title' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:services,slug,' . $service->id,
        'menu_title' => 'nullable|string|max:255',
        'short_description' => 'nullable|string',
        'description' => 'nullable|string',

        'meta_title' => 'nullable|string|max:255',
        'meta_keywords' => 'nullable|string',
        'meta_description' => 'nullable|string',

        'show_in_menu' => 'nullable|in:0,1',
        'status' => 'nullable|in:0,1',

        'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'intro_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'trusted_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'seo_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'why_choose_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'partner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'cta_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'cards.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
    ]);

    $service->title = $request->title;
    $service->slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);
    $service->menu_title = $request->menu_title;
    $service->short_description = $request->short_description;
    $service->description = $request->description;

    $service->hero_title = $request->hero_title;
    $service->intro_content = $request->intro_content;

    $service->trusted_title = $request->trusted_title;
    $service->trusted_description = $request->trusted_description;

    $service->services_section_title = $request->services_section_title;

    $service->seo_title = $request->seo_title;
    $service->seo_description = $request->seo_description;

    $service->why_choose_title = $request->why_choose_title;

    $service->process_title = $request->process_title;

    $service->partner_title = $request->partner_title;
    $service->partner_points = $request->partner_points;

    $service->faq_title = $request->faq_title;

    $service->cta_title = $request->cta_title;
    $service->cta_description = $request->cta_description;
    $service->cta_button_text = $request->cta_button_text;
    $service->cta_button_link = $request->cta_button_link;

    $service->meta_title = $request->meta_title;
    $service->meta_keywords = $request->meta_keywords;
    $service->meta_description = $request->meta_description;

    $service->show_in_menu = $request->show_in_menu ?? 1;
    $service->status = $request->status ?? 1;

    $uploadPath = 'uploads/services';

    if ($request->hasFile('hero_image')) {
        $file = $request->file('hero_image');
        $name = time() . '_hero_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($uploadPath), $name);
        $service->hero_image = $uploadPath . '/' . $name;
    }

    if ($request->hasFile('intro_image')) {
        $file = $request->file('intro_image');
        $name = time() . '_intro_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($uploadPath), $name);
        $service->intro_image = $uploadPath . '/' . $name;
    }

    if ($request->hasFile('trusted_image')) {
        $file = $request->file('trusted_image');
        $name = time() . '_trusted_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($uploadPath), $name);
        $service->trusted_image = $uploadPath . '/' . $name;
    }

    if ($request->hasFile('seo_image')) {
        $file = $request->file('seo_image');
        $name = time() . '_seo_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($uploadPath), $name);
        $service->seo_image = $uploadPath . '/' . $name;
    }

    if ($request->hasFile('why_choose_image')) {
        $file = $request->file('why_choose_image');
        $name = time() . '_why_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($uploadPath), $name);
        $service->why_choose_image = $uploadPath . '/' . $name;
    }

    if ($request->hasFile('partner_image')) {
        $file = $request->file('partner_image');
        $name = time() . '_partner_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($uploadPath), $name);
        $service->partner_image = $uploadPath . '/' . $name;
    }

    if ($request->hasFile('cta_image')) {
        $file = $request->file('cta_image');
        $name = time() . '_cta_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($uploadPath), $name);
        $service->cta_image = $uploadPath . '/' . $name;
    }

    // menu items
    $menuItems = [];
    if ($request->has('menu_items') && is_array($request->menu_items)) {
        foreach ($request->menu_items as $item) {
            if (!empty($item['title'])) {
                $menuItems[] = [
                    'title' => $item['title'],
                ];
            }
        }
    }
    $service->menu_items = $menuItems;

    // cards
    $cards = [];
    if ($request->has('cards') && is_array($request->cards)) {
        foreach ($request->cards as $index => $card) {
            if (
                empty($card['title']) &&
                empty($card['description']) &&
                !$request->hasFile("cards.$index.image") &&
                empty($card['old_image'])
            ) {
                continue;
            }

            $cardImage = $card['old_image'] ?? null;

            if ($request->hasFile("cards.$index.image")) {
                $file = $request->file("cards.$index.image");
                $name = time() . '_card_' . $index . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($uploadPath), $name);
                $cardImage = $uploadPath . '/' . $name;
            }

            $cards[] = [
                'title' => $card['title'] ?? '',
                'description' => $card['description'] ?? '',
                'image' => $cardImage,
            ];
        }
    }
    $service->cards = $cards;

    // features
    $features = [];
    if ($request->has('features') && is_array($request->features)) {
        foreach ($request->features as $feature) {
            if (empty($feature['title']) && empty($feature['description'])) {
                continue;
            }

            $features[] = [
                'title' => $feature['title'] ?? '',
                'description' => $feature['description'] ?? '',
            ];
        }
    }
    $service->features = $features;

    // process steps
    $processSteps = [];
    if ($request->has('process_steps') && is_array($request->process_steps)) {
        foreach ($request->process_steps as $step) {
            if (empty($step['number']) && empty($step['title'])) {
                continue;
            }

            $processSteps[] = [
                'number' => $step['number'] ?? '',
                'title' => $step['title'] ?? '',
            ];
        }
    }
    $service->process_steps = $processSteps;

    // faqs
    $faqs = [];
    if ($request->has('faqs') && is_array($request->faqs)) {
        foreach ($request->faqs as $faq) {
            if (empty($faq['question']) && empty($faq['answer'])) {
                continue;
            }

            $faqs[] = [
                'question' => $faq['question'] ?? '',
                'answer' => $faq['answer'] ?? '',
            ];
        }
    }
    $service->faqs = $faqs;

    $service->save();

    return redirect()
        ->route('admin.services.index')
        ->with('success', 'Service updated successfully.');
}

public function delete($id)
{
    $service =Service::findOrFail($id);
    $service->delete();

    return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
}

   public function show($slug)
{
    $service = Service::where('slug', $slug)
        ->where('status', 1)
        ->firstOrFail();

    $menuItems = Service::where('status', 1)
        ->select('title', 'slug')
        ->orderBy('id', 'asc')
        ->get();

    return view($this->theme . 'Service-details', compact('service', 'menuItems'));
}
}