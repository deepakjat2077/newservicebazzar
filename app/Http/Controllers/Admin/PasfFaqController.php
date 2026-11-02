<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryPageFaq;
use App\Models\Location;
use App\Services\CategoryFaqWriter;
use Illuminate\Http\Request;

/**
 * Admin > People Also Search: the "People also search for" Q&As written by
 * seo:pasf-faq for the trial categories' city pages. Nothing shows on the
 * site until it is approved here; edits and approvals clear that page's cache.
 */
class PasfFaqController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::whereIn('id', (array) config('seo.pasf_faq_categories', []))->orderBy('name')->get();
        $category = $categories->firstWhere('id', (int) $request->category) ?? $categories->first();

        $rows = collect();
        $cities = collect();
        if ($category) {
            $rows = CategoryPageFaq::with('location')->where('category_id', $category->id)->get()->keyBy('location_id');
            $cities = Location::where('status', 1)
                ->withCount(['listings as live_count' => fn ($q) => $q->where('status', 1)->where('category_id', $category->id)])
                ->having('live_count', '>', 0)
                ->orderByDesc('live_count')->orderBy('name')
                ->get();
        }

        return view('admin.seo.pasf.index', [
            'categories' => $categories,
            'category'   => $category,
            'rows'       => $rows,
            'cities'     => $cities,
        ]);
    }

    public function edit($id)
    {
        $row = CategoryPageFaq::with(['category', 'location'])->findOrFail($id);
        abort_unless(CategoryPageFaq::enabledFor($row->category_id), 404);

        return view('admin.seo.pasf.edit', ['row' => $row]);
    }

    public function update(Request $request, $id)
    {
        $row = CategoryPageFaq::findOrFail($id);
        abort_unless(CategoryPageFaq::enabledFor($row->category_id), 404);

        $request->validate([
            'faqs'            => 'array',
            'faqs.*.question' => 'nullable|string|max:200',
            'faqs.*.answer'   => 'nullable|string|max:600',
            'faqs.*.keywords'   => 'nullable|array',
            'faqs.*.keywords.*' => 'nullable|string|max:120',
        ]);

        $faqs = [];
        foreach ((array) $request->faqs as $f) {
            $q = trim(preg_replace('/\s+/u', ' ', (string) ($f['question'] ?? '')));
            $a = trim(preg_replace('/\s+/u', ' ', (string) ($f['answer'] ?? '')));
            if (!empty($f['remove']) || $q === '' || $a === '') {
                continue;
            }
            $kw = array_values(array_filter(array_map(fn ($k) => trim((string) $k), (array) ($f['keywords'] ?? []))));
            $faqs[] = ['keyword' => $kw[0] ?? '', 'keywords' => $kw, 'question' => $q, 'answer' => $a];
        }

        $row->faqs = $faqs;
        if ($request->input('action') === 'approve' && $faqs) {
            $row->fill(['status' => 'approved', 'approved_at' => now(), 'approved_by' => auth('admin')->id(), 'error' => null]);
        }
        $row->save();
        $row->forgetPageCache();
        if ($row->status === 'approved') {
            $row->requestReindex();
        }

        $msg = $row->status === 'approved'
            ? 'Saved. It is live on the page now, and the page goes to Google (Indexing API) within the hour.'
            : 'Saved as draft. Approve it to show it on the page.';

        return redirect()->route('admin.seo.pasf.edit', $row->id)->withAlert(['success', $msg]);
    }

    public function unpublish($id)
    {
        $row = CategoryPageFaq::findOrFail($id);
        $row->update(['status' => 'draft', 'approved_at' => null, 'approved_by' => null]);
        $row->forgetPageCache();

        return back()->withAlert(['success', 'Removed from the page. It is a draft again.']);
    }

    /** Write (or rewrite) one city now. Takes 10-30 seconds: one Google lookup, one AI call. */
    public function generate(Request $request, CategoryFaqWriter $writer)
    {
        $category = Category::findOrFail((int) $request->category_id);
        abort_unless(CategoryPageFaq::enabledFor($category->id), 404);
        $location = Location::findOrFail((int) $request->location_id);

        @set_time_limit(120);
        $row = $writer->generate($category, $location);
        $row->forgetPageCache();

        if ($row->error) {
            return back()->withAlert(['danger', $location->name . ': ' . $row->error]);
        }

        return redirect()->route('admin.seo.pasf.edit', $row->id)
            ->withAlert(['success', 'Written. Check the answers, then Approve.']);
    }
}
