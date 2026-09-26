<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificatePackage extends Model
{
    protected $fillable = ['name', 'image', 'gallery', 'benefits', 'highlights', 'plans', 'pricing_type', 'positions', 'validity_days', 'needs_ad_details', 'perk_type', 'status'];

    protected $casts = ['plans' => 'array', 'gallery' => 'array', 'status' => 'boolean', 'needs_ad_details' => 'boolean'];

    public function positionPrices()
    {
        return $this->hasMany(PackagePositionPrice::class);
    }

    public function isPositionPriced(): bool
    {
        return $this->pricing_type === 'position';
    }

    /** Per city/area prices for the package's own plan list. */
    public function isLocationPriced(): bool
    {
        return $this->pricing_type === 'location';
    }

    /** Either mode where the buyer must choose a city and area. */
    public function needsLocation(): bool
    {
        return $this->isPositionPriced() || $this->isLocationPriced();
    }

    /**
     * The tiers a city/area row holds a price for: "Top 1..N" for position
     * pricing, otherwise the package's own plan labels.
     */
    public function tierLabels(): array
    {
        if ($this->isPositionPriced()) {
            return array_map(fn ($i) => 'Top ' . $i, range(1, max(1, (int) $this->positions)));
        }

        return array_column($this->planList(), 'label');
    }

    /**
     * ['Jaipur' => ['' => ['Top 1' => 5000, …], 'Mansarovar' => [...]]] for the
     * city/area pickers. Tiers keep the package's own order.
     */
    public function positionPriceTree(): array
    {
        $order = array_flip($this->tierLabels());

        $rows = $this->positionPrices()->orderBy('city')->orderBy('area')->get()
            ->sortBy(fn ($r) => $order[$r->tier] ?? 99);

        $tree = [];
        foreach ($rows as $row) {
            $tree[$row->city][$row->area][$row->tier] = (float) $row->price;
        }
        return $tree;
    }

    public function benefitList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $this->benefits))));
    }

    public function highlightList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $this->highlights))));
    }

    /**
     * Plans as [{label, price}]. Older rows stored only `years`, so build the
     * label from that when it is missing.
     */
    public function planList(): array
    {
        return collect($this->plans ?? [])->map(function ($p) {
            $label = $p['label'] ?? null;
            if (!$label && !empty($p['years'])) {
                $label = $p['years'] . ($p['years'] == 1 ? ' Year' : ' Years');
            }
            return ['label' => (string) $label, 'price' => $p['price'] ?? 0];
        })->values()->all();
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('assets/images/certificate_package/' . $this->image) : null;
    }

    /** Main image first, then the extra slides. */
    public function imageUrls(): array
    {
        return collect([$this->image])->merge($this->gallery ?? [])->filter()
            ->map(fn ($f) => asset('assets/images/certificate_package/' . $f))->values()->all();
    }

    /**
     * Resolves what a buyer's chosen option actually costs, from the DB — the
     * price is never taken from the submitted form.
     *
     * $choice: ['plan' => index] or ['position' => n, 'city' => .., 'area' => ..]
     * Returns ['label','price','days','city','area','position'] or null.
     */
    public function resolveChoice(array $choice): ?array
    {
        if ($this->needsLocation()) {
            $city = (string) ($choice['city'] ?? '');
            $area = (string) ($choice['area'] ?? '');
            $tier = (string) ($choice['tier'] ?? '');

            if ($city === '' || $tier === '') {
                return null;
            }

            // The area's own price, else the city-wide one, else — for plan
            // packages — the package's own price for that plan.
            $row = $this->positionPrices()->where('city', $city)->where('tier', $tier)
                ->whereIn('area', array_unique([$area, '']))
                ->orderByRaw('area = ? DESC', [$area])
                ->first();

            $base = collect($this->planList())->firstWhere('label', $tier);

            if (!$row && !$base) {
                return null;
            }

            $price = $row ? (float) $row->price : (float) $base['price'];

            return [
                'label'    => $tier . ' — ' . $city . ($area ? ', ' . $area : ' (whole city)'),
                'tier'     => $tier,
                'price'    => $price,
                'days'     => (int) (($row->days ?? null) ?: self::planDays($tier, $this->validity_days)),
                'city'     => $city,
                'area'     => $area,
                'position' => $row->position ?? null,
            ];
        }

        $plans = $this->planList();
        $index = (int) ($choice['plan'] ?? -1);

        if (!isset($plans[$index])) {
            return null;
        }

        return [
            'label'    => $plans[$index]['label'],
            'tier'     => $plans[$index]['label'],
            'price'    => (float) $plans[$index]['price'],
            'days'     => self::planDays($plans[$index]['label'], $this->validity_days),
            'city'     => null,
            'area'     => null,
            'position' => null,
        ];
    }

    /** "2 Years" -> 730, "6 Months" -> 180, "45 Days" -> 45. */
    public static function planDays(string $label, $fallback = 365): int
    {
        if (preg_match('/(\d+)\s*(year|yr|month|mon|week|day)/i', $label, $m)) {
            $n = (int) $m[1];
            return match (strtolower(substr($m[2], 0, 3))) {
                'yea', 'yr'  => $n * 365,
                'mon' => $n * 30,
                'wee' => $n * 7,
                default => $n,
            };
        }

        return (int) ($fallback ?: 365);
    }

    public function startingPrice()
    {
        return collect($this->plans ?? [])->min('price');
    }
}
