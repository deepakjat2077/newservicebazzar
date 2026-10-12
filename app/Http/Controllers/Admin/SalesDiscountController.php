<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificatePackage;
use App\Models\SalesDiscount;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

/** The discounts the sales team can pick from in the cart. */
class SalesDiscountController extends Controller
{
    public function index()
    {
        $discounts = SalesDiscount::latest('id')->get();
        $packages  = CertificatePackage::pluck('name', 'id');
        $used      = SalesOrder::whereNotNull('discount_id')->selectRaw('discount_id, count(*) as n')->groupBy('discount_id')->pluck('n', 'discount_id');

        return view('admin.sales_discounts.index', compact('discounts', 'packages', 'used'));
    }

    public function create()
    {
        return view('admin.sales_discounts.form', [
            'discount' => new SalesDiscount(['type' => SalesDiscount::PERCENT, 'is_active' => true]),
            'packages' => CertificatePackage::where('status', 1)->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        SalesDiscount::create($this->validated($request));

        return redirect()->route('admin.sales_discounts.index')->withAlert(['success', 'Discount created.']);
    }

    public function edit($id)
    {
        return view('admin.sales_discounts.form', [
            'discount' => SalesDiscount::findOrFail($id),
            'packages' => CertificatePackage::where('status', 1)->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, $id)
    {
        SalesDiscount::findOrFail($id)->update($this->validated($request));

        return redirect()->route('admin.sales_discounts.index')->withAlert(['success', 'Discount updated.']);
    }

    public function toggle($id)
    {
        $discount = SalesDiscount::findOrFail($id);
        $discount->update(['is_active' => !$discount->is_active]);

        return back()->withAlert(['success', $discount->name . ($discount->is_active ? ' is on.' : ' is off.')]);
    }

    /** Past orders keep their own copy of the name and amount, so deleting is safe. */
    public function destroy($id)
    {
        SalesDiscount::findOrFail($id)->delete();

        return back()->withAlert(['success', 'Discount deleted.']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'          => 'required|string|max:120',
            'type'          => 'required|in:percent,flat',
            'value'         => 'required|numeric|gt:0|max:1000000',
            'scope'         => 'required|in:all,selected',
            'package_ids'   => 'required_if:scope,selected|array',
            'package_ids.*' => 'integer|exists:certificate_packages,id',
            'starts_on'     => 'nullable|date',
            'ends_on'       => 'nullable|date|after_or_equal:starts_on',
        ], [
            'package_ids.required_if' => 'Pick at least one package, or choose "All packages".',
            'ends_on.after_or_equal'  => 'The end date cannot be before the start date.',
        ]);

        if ($data['type'] === SalesDiscount::PERCENT && (float) $data['value'] > 100) {
            abort(back()->withErrors(['value' => 'A percentage cannot be more than 100.'])->withInput());
        }

        return [
            'name'        => trim($data['name']),
            'type'        => $data['type'],
            'value'       => $data['value'],
            'package_ids' => $data['scope'] === 'selected' ? array_values(array_map('intval', $data['package_ids'])) : null,
            'starts_at'   => !empty($data['starts_on']) ? \Carbon\Carbon::parse($data['starts_on'])->startOfDay() : null,
            'ends_at'     => !empty($data['ends_on']) ? \Carbon\Carbon::parse($data['ends_on'])->endOfDay() : null,
            'is_active'   => $request->boolean('is_active'),
        ];
    }
}
