<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificatePackage;
use App\Models\Listing;
use App\Models\Location;
use App\Models\PackagePositionPrice;
use Illuminate\Http\Request;

class CertificatePackageController extends Controller
{
    private const DIR = 'assets/images/certificate_package';

    public function store(Request $request)
    {
        $this->validation($request, true);
        $this->fill(new CertificatePackage(), $request);
        return back()->withAlert(['success', 'Package created successfully']);
    }

    public function update(Request $request, $id)
    {
        $this->validation($request, false);
        $this->fill(CertificatePackage::findOrFail($id), $request);
        return back()->withAlert(['success', 'Package updated successfully']);
    }

    public function status($id)
    {
        $pkg = CertificatePackage::findOrFail($id);
        $pkg->status = !$pkg->status;
        $pkg->save();
        return back()->withAlert(['success', 'Package status updated successfully']);
    }

    public function delete($id)
    {
        $pkg = CertificatePackage::findOrFail($id);
        $this->removeImage($pkg->image);
        foreach ($pkg->gallery ?? [] as $f) {
            $this->removeImage($f);
        }
        $pkg->delete();
        return back()->withAlert(['success', 'Package deleted successfully']);
    }

    protected function fill(CertificatePackage $pkg, Request $request): void
    {
        $pkg->name = $request->name;
        $pkg->benefits = $request->benefits;

        $pkg->highlights = $request->highlights;
        $pkg->pricing_type = in_array($request->pricing_type, ['position', 'location'], true)
            ? $request->pricing_type : 'plan';
        $pkg->needs_ad_details = $request->boolean('needs_ad_details');
        $pkg->positions = max(1, min(10, (int) ($request->positions ?: 3)));

        $plans = [];
        foreach ((array) $request->input('plan_label', []) as $i => $label) {
            $label = trim((string) $label);
            $price = $request->input('plan_price.' . $i);
            if ($label === '' || $price === null || $price === '') {
                continue;
            }
            $plans[] = ['label' => $label, 'price' => (float) $price];
        }
        $pkg->plans = $plans;

        if ($request->hasFile('image')) {
            $dir = public_path(self::DIR);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $file = uniqid('cert_') . '.webp';
            $this->toWebp($request->file('image')->getRealPath(), $dir . '/' . $file);
            $this->removeImage($pkg->image);
            $pkg->image = $file;
        }

        $gallery = $pkg->gallery ?? [];
        if ($request->boolean('clear_gallery')) {
            foreach ($gallery as $f) {
                $this->removeImage($f);
            }
            $gallery = [];
        }
        foreach ((array) $request->file('gallery', []) as $upload) {
            $file = uniqid('cert_') . '.webp';
            $this->toWebp($upload->getRealPath(), public_path(self::DIR) . '/' . $file);
            $gallery[] = $file;
        }
        $pkg->gallery = array_values($gallery);

        $pkg->save();
    }

    protected function validation(Request $request, bool $creating): void
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'benefits'     => 'nullable|string',
            'image'        => ($creating ? 'required' : 'nullable') . '|image|max:10240',
            'highlights'   => 'nullable|string',
            'gallery'      => 'nullable|array|max:6',
            'gallery.*'    => 'image|max:10240',
            'pricing_type' => 'nullable|in:plan,position,location',
            'positions'    => 'nullable|integer|min:1|max:10',
            'plan_label'   => 'nullable|array',
            'plan_label.*' => 'nullable|string|max:30',
            'plan_price'   => 'nullable|array',
            'plan_price.*' => 'nullable|numeric|gte:0',
        ], [
            'image.required' => 'Please upload the package image.',
        ]);
    }

    /** Price-per-city/area screen for a position-priced package. */
    public function positions(Request $request, $id)
    {
        $package = CertificatePackage::findOrFail($id);

        $rows = $package->positionPrices()
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->city))
            ->orderBy('city')->orderBy('area')
            ->get()
            ->groupBy(fn ($r) => $r->city . '|' . $r->area);

        $tiers = $package->tierLabels();

        $cities = Location::where('status', 1)->orderBy('name')->pluck('name');
        if ($cities->isEmpty()) {
            $cities = Listing::query()->whereNotNull('city')->where('city', '!=', '')
                ->distinct()->orderBy('city')->limit(200)->pluck('city');
        }

        return view('admin.package.positions', compact('package', 'rows', 'cities', 'tiers'));
    }

    /** Saves one city/area row: a price for each tier at once. */
    public function storePosition(Request $request, $id)
    {
        $package = CertificatePackage::findOrFail($id);

        $request->validate([
            'city'    => 'required|string|max:100',
            'area'    => 'nullable|string|max:150',
            'price'   => 'required|array|min:1',
            'price.*' => 'nullable|numeric|gte:0',
            'days'    => 'nullable|array',
            'days.*'  => 'nullable|integer|min:1|max:3650',
        ]);

        $city = trim($request->city);
        $area = trim((string) $request->area);
        $tiers = $package->tierLabels();
        $saved = 0;

        foreach ($tiers as $i => $tier) {
            $price = $request->input('price.' . $i);
            $days = $request->input('days.' . $i);

            $key = [
                'certificate_package_id' => $package->id,
                'city' => $city, 'area' => $area, 'tier' => $tier,
            ];

            if ($price === null || $price === '') {
                PackagePositionPrice::where($key)->delete();
                continue;
            }

            PackagePositionPrice::updateOrCreate($key, [
                'price'    => (float) $price,
                'days'     => $days !== null && $days !== '' ? (int) $days : null,
                'position' => $package->isPositionPriced() ? $i + 1 : null,
            ]);
            $saved++;
        }

        return back()->withAlert(['success', $saved
            ? 'Prices saved for ' . $city . ($area ? ' — ' . $area : ' (whole city)')
            : 'Nothing to save — enter at least one price.']);
    }

    public function deletePosition($id, Request $request)
    {
        $package = CertificatePackage::findOrFail($id);
        $package->positionPrices()
            ->where('city', $request->city)->where('area', (string) $request->area)
            ->delete();

        return back()->withAlert(['success', 'Prices removed.']);
    }

    /** Area suggestions for the city, taken from existing listings. */
    public function areaSuggest(Request $request)
    {
        $areas = Listing::query()
            ->where('city', $request->city)
            ->whereNotNull('area')->where('area', '!=', '')
            ->distinct()->orderBy('area')->limit(300)->pluck('area');

        return response()->json($areas);
    }

    private function removeImage(?string $file): void
    {
        if ($file && is_file($path = public_path(self::DIR . '/' . $file))) {
            @unlink($path);
        }
    }

    private function toWebp(string $src, string $dest, int $max = 1400): void
    {
        $info = @getimagesize($src);
        $img = match ($info['mime'] ?? '') {
            'image/jpeg' => @imagecreatefromjpeg($src),
            'image/png'  => @imagecreatefrompng($src),
            'image/webp' => @imagecreatefromwebp($src),
            'image/gif'  => @imagecreatefromgif($src),
            default      => null,
        };
        if (!$img) {
            copy($src, $dest);
            return;
        }
        [$w, $h] = $info;
        $scale = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagewebp($dst, $dest, 85);
        imagedestroy($img);
        imagedestroy($dst);
    }
}
