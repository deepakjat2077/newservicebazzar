<?php

namespace App\Support;

use App\Models\Listing;
use App\Models\ListingSalesPackage;

/**
 * Turns a paid sales package into the thing the business actually gets: a
 * certificate drawn in their own name, a verified/trusted badge, and so on.
 */
class SalesPerks
{
    public const DIR = 'assets/images/sales_certificates';

    /**
     * Records a purchase against a listing and hands over what it buys. Used
     * both by the payment callback and by free (zero-price) plans.
     */
    public static function activate(Listing $listing, $package, array $choice, $payment = null): ListingSalesPackage
    {
        $days = (int) ($choice['days'] ?? 365);

        $bought = ListingSalesPackage::create([
            'listing_id'             => $listing->id,
            'certificate_package_id' => $package->id,
            'payment_id'             => $payment?->id,
            'plan'                   => $choice['label'] ?? 'Plan',
            'price'                  => $choice['price'] ?? 0,
            'city'                   => $choice['city'] ?? null,
            'area'                   => $choice['area'] ?? null,
            'position'               => $choice['position'] ?? null,
            'days'                   => $days,
            'starts_at'              => now(),
            'expires_at'             => now()->addDays($days),
        ]);

        self::apply($bought);

        // Anyone on a live plan carries the star on their listing.
        self::syncStar($listing->id);

        if ($package->needs_ad_details) {
            \App\Models\ListingAdBanner::create([
                'listing_id'               => $listing->id,
                'certificate_package_id'   => $package->id,
                'listing_sales_package_id' => $bought->id,
                'city'                     => $bought->city,
                'area'                     => $bought->area,
                'category_id'              => $listing->category_id,
                'status'                   => \App\Models\ListingAdBanner::PENDING,
                'starts_at'                => $bought->starts_at,
                'expires_at'               => $bought->expires_at,
            ]);
        }

        return $bought;
    }

    /** Called once a purchase is credited: hands over what the package gives. */
    public static function apply(ListingSalesPackage $bought): void
    {
        $package = $bought->package;
        $listing = Listing::find($bought->listing_id);

        if (!$package || !$listing) {
            return;
        }

        switch ($package->perk_type) {
            case 'certificate':
                $file = self::drawCertificate($listing, $bought);
                if ($file) {
                    $bought->asset = $file;
                    $bought->save();
                }
                break;

            case 'top_position':
                // Only book a slot when the position is unambiguous. A campaign
                // bought across several areas books its slots per area from the
                // order itself, which knows each one.
                $tier = $bought->position
                    ? 'Top ' . $bought->position
                    : (preg_match('/^Top \d+$/', (string) $bought->plan) ? $bought->plan : null);

                if (!$tier) {
                    break;
                }

                \App\Models\ListingPositionSlot::updateOrCreate([
                    'certificate_package_id' => $package->id,
                    'city'                   => $bought->city ?: $listing->city,
                    'pincode'                => $listing->pincode,
                    'tier'                   => $tier,
                    'category_id'            => $listing->category_id,
                ], [
                    'listing_id'               => $listing->id,
                    'listing_sales_package_id' => $bought->id,
                    'area'                     => $bought->area ?: $listing->area,
                    'starts_at'                => $bought->starts_at,
                    'expires_at'               => $bought->expires_at,
                ]);

                self::syncTopRank($listing->id);
                break;

            case 'badge_verified':
                $listing->verified = 1;
                $listing->save();
                break;

            case 'badge_trusted':
                $listing->trust = 1;
                $listing->save();
                break;
        }
    }

    /**
     * The star beside a business name: on while it holds any live package,
     * off once everything it bought has run out. A star set by admin on a
     * business that never bought anything is left alone.
     */
    public static function syncStar(int $listingId): void
    {
        $everBought = ListingSalesPackage::where('listing_id', $listingId)->exists();

        if (!$everBought) {
            return;
        }

        $live = ListingSalesPackage::where('listing_id', $listingId)
            ->where('expires_at', '>', now())->exists();

        $listing = Listing::find($listingId);

        if ($listing && (bool) $listing->is_star !== $live) {
            $listing->is_star = $live ? 1 : 0;
            $listing->save();
        }
    }

    /**
     * Paid positions live in listing_position_slots and only apply to the area
     * they were bought for — search reads them there. The admin's own
     * top_rank field is left alone.
     */
    public static function syncTopRank(int $listingId): void
    {
        // Kept for callers; nothing to sync now that position is per area.
    }

    /**
     * Takes back badges whose plans have run out — unless the listing still
     * holds another live purchase of the same kind.
     */
    public static function expire(): int
    {
        $flags = ['badge_verified' => 'verified', 'badge_trusted' => 'trust'];
        $reverted = 0;

        foreach ($flags as $perk => $column) {
            $rows = ListingSalesPackage::query()
                ->whereHas('package', fn ($q) => $q->where('perk_type', $perk))
                ->where('expires_at', '<=', now())
                ->get();

            foreach ($rows->unique('listing_id') as $row) {
                $stillPaid = ListingSalesPackage::where('listing_id', $row->listing_id)
                    ->where('expires_at', '>', now())
                    ->whereHas('package', fn ($q) => $q->where('perk_type', $perk))
                    ->exists();

                if ($stillPaid) {
                    continue;
                }

                $listing = Listing::find($row->listing_id);
                if ($listing && $listing->{$column}) {
                    $listing->{$column} = 0;
                    $listing->save();
                    $reverted++;
                }
            }
        }

        // Stars follow the plans: on while live, off once they lapse.
        foreach (ListingSalesPackage::query()->get()->unique('listing_id') as $row) {
            self::syncStar($row->listing_id);
        }

        return $reverted;
    }

    /** The business's own certificate: logo, gold seal, stars, their name. */
    public static function drawCertificate(Listing $listing, ListingSalesPackage $bought): ?string
    {
        $dir = public_path(self::DIR);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            return null;
        }

        $W = 1200;
        $H = 850;
        $im = imagecreatetruecolor($W, $H);

        $c = fn ($r, $g, $b, $a = 0) => imagecolorallocatealpha($im, $r, $g, $b, $a);
        $white = $c(255, 255, 255);
        $red = $c(206, 17, 38);
        $navy = $c(10, 25, 60);
        $gold = $c(212, 160, 23);
        $gold2 = $c(245, 200, 70);
        $grey = $c(90, 90, 90);

        imagefill($im, 0, 0, $white);

        $bold = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $regular = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        $serif = '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf';

        imagesetthickness($im, 6);
        imagerectangle($im, 25, 25, $W - 26, $H - 26, $navy);
        imagesetthickness($im, 2);
        imagerectangle($im, 40, 40, $W - 41, $H - 41, $red);

        imagefilledpolygon($im, [0, $H - 90, (int) ($W * 0.6), $H - 40, (int) ($W * 0.6), $H, 0, $H], $navy);
        imagefilledpolygon($im, [(int) ($W * 0.45), $H, $W, $H - 120, $W, $H], $red);

        $centre = function (int $size, string $font, string $text, int $y, $colour) use ($im, $W) {
            $box = imagettfbbox($size, 0, $font, $text);
            imagettftext($im, $size, 0, (int) (($W - ($box[2] - $box[0])) / 2), $y, $colour, $font, $text);
        };

        $fit = function (string $text, int $size, string $font, int $maxWidth) {
            while ($size > 12) {
                $box = imagettfbbox($size, 0, $font, $text);
                if ($box[2] - $box[0] <= $maxWidth) {
                    break;
                }
                $size--;
            }
            return $size;
        };

        $top = 170;
        $logo = @imagecreatefrompng(public_path('assets/images/logo/logo.png'));
        if ($logo) {
            $lw = imagesx($logo);
            $lh = imagesy($logo);
            $nw = 360;
            $nh = (int) ($lh * $nw / $lw);
            imagecopyresampled($im, $logo, (int) (($W - $nw) / 2), 70, 0, 0, $nw, $nh, $lw, $lh);
            imagedestroy($logo);
            $top = 70 + $nh + 20;
        } else {
            $centre(40, $bold, 'Service Bazzar', 130, $red);
        }

        // gold seal
        $cx = (int) ($W / 2);
        $cy = $top + 95;
        imagefilledellipse($im, $cx, $cy, 190, 190, $gold);
        imagefilledellipse($im, $cx, $cy, 160, 160, $gold2);
        imagefilledellipse($im, $cx, $cy, 130, 130, $gold);

        foreach ([['SB', 44, $serif, 10], ['VERIFIED', 13, $bold, 38], [date('Y'), 12, $bold, -40]] as [$text, $size, $font, $dy]) {
            $box = imagettfbbox($size, 0, $font, $text);
            imagettftext($im, $size, 0, (int) ($cx - ($box[2] - $box[0]) / 2), $cy + $dy, $white, $font, $text);
        }

        // five stars
        $starY = $cy + 140;
        $star = function (int $x, int $y, int $r) use ($im) {
            $points = [];
            for ($i = 0; $i < 10; $i++) {
                $angle = deg2rad(-90 + $i * 36);
                $rr = $i % 2 ? $r * 0.45 : $r;
                $points[] = (int) ($x + $rr * cos($angle));
                $points[] = (int) ($y + $rr * sin($angle));
            }
            imagefilledpolygon($im, $points, imagecolorallocate($im, 245, 130, 20));
        };
        for ($i = -2; $i <= 2; $i++) {
            $star($cx + $i * 62, $starY, 26);
        }

        $centre(20, $regular, 'CERTIFICATE OF EXCELLENCE', $starY + 70, $grey);

        $name = $listing->name;
        $centre($fit($name, 40, $bold, $W - 200), $bold, $name, $starY + 130, $navy);

        $place = trim(($listing->area ? $listing->area . ', ' : '') . ($listing->city ?? ''), ', ');
        if ($place !== '') {
            $centre(18, $regular, $place, $starY + 168, $grey);
        }

        $centre(15, $regular, 'Customer Ratings & Reviews on Service Bazzar · Valid till '
            . optional($bought->expires_at)->format('d M Y'), $starY + 200, $grey);

        $file = 'cert_' . $bought->id . '_' . time() . '.webp';
        $ok = @imagewebp($im, $dir . '/' . $file, 90);
        imagedestroy($im);

        return $ok ? $file : null;
    }
}
