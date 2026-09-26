<?php

namespace App\Support;

use App\Models\Listing;

/**
 * Package previews drawn with the business's own name, rating, address and
 * photos — so a rep pitching "SB Verified" shows that shop's listing carrying
 * the badge, not a generic sample.
 *
 * Images are cached per listing + variant and redrawn when the listing changes.
 */
class SalesPreview
{
    public const DIR = 'assets/images/sales_previews';

    private const PHOTO_DIR = 'assets/images/listing/photos';

    /** Which drawing a package's perk needs. Null = use the package's own image. */
    public static function variantFor($package): ?string
    {
        return match ($package->perk_type ?? 'none') {
            'badge_verified' => 'verified',
            'badge_trusted'  => 'trusted',
            'top_position'   => 'top',
            'banner'         => 'banner',
            'certificate'    => 'certificate',
            default          => null,
        };
    }

    /** Cached preview URL for this listing + variant, drawing it if needed. */
    public static function url(Listing $listing, string $variant): ?string
    {
        $dir = public_path(self::DIR);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            return null;
        }

        $file = $listing->id . '_' . $variant . '.webp';
        $path = $dir . '/' . $file;

        $stale = !is_file($path)
            || ($listing->updated_at && filemtime($path) < $listing->updated_at->timestamp);

        if ($stale) {
            try {
                $drawn = $variant === 'certificate'
                    ? self::drawCertificate($listing, $path)
                    : self::drawPhone($listing, $variant, $path);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Sales preview could not be drawn', [
                    'listing' => $listing->id,
                    'variant' => $variant,
                    'error'   => $e->getMessage(),
                ]);
                $drawn = false;
            }

            // Fall back to whatever is already on disk; the caller uses the
            // package's own image when there is nothing.
            if (!$drawn && !is_file($path)) {
                return null;
            }
        }

        return asset(self::DIR . '/' . $file) . '?v=' . filemtime($path);
    }

    /** Draws an image cropped to exactly fill the given box (like object-fit: cover). */
    private static function coverInto($dst, string $file, int $x, int $y, int $w, int $h): bool
    {
        $src = @imagecreatefromwebp($file);

        if (!$src) {
            return false;
        }

        $sw = imagesx($src);
        $sh = imagesy($src);
        $scale = max($w / $sw, $h / $sh);
        $cropW = (int) round($w / $scale);
        $cropH = (int) round($h / $scale);

        imagecopyresampled(
            $dst, $src, $x, $y,
            (int) (($sw - $cropW) / 2), (int) (($sh - $cropH) / 2),
            $w, $h, $cropW, $cropH
        );
        imagedestroy($src);

        return true;
    }

    /** The business's own listing screen, with whatever the package adds to it. */
    private static function drawPhone(Listing $listing, string $variant, string $dest): bool
    {
        $W = 800;
        $H = 1000;
        $im = imagecreatetruecolor($W, $H);

        $c = fn ($r, $g, $b, $a = 0) => imagecolorallocatealpha($im, $r, $g, $b, $a);
        $white = $c(255, 255, 255);
        $dark = $c(34, 34, 34);
        $grey = $c(120, 120, 120);
        $lg = $c(228, 228, 228);
        $green = $c(22, 140, 60);
        $blue = $c(0, 118, 215);
        $gold = $c(245, 190, 20);
        $orange = $c(245, 140, 20);

        imagefill($im, 0, 0, $white);

        $bold = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $regular = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

        $rr = function ($x1, $y1, $x2, $y2, $r, $col) use ($im) {
            imagefilledrectangle($im, $x1 + $r, $y1, $x2 - $r, $y2, $col);
            imagefilledrectangle($im, $x1, $y1 + $r, $x2, $y2 - $r, $col);
            foreach ([[$x1 + $r, $y1 + $r], [$x2 - $r, $y1 + $r], [$x1 + $r, $y2 - $r], [$x2 - $r, $y2 - $r]] as $p) {
                imagefilledellipse($im, $p[0], $p[1], $r * 2, $r * 2, $col);
            }
        };
        $t = fn ($size, $font, $x, $y, $col, $text) => imagettftext($im, $size, 0, (int) $x, (int) $y, $col, $font, $text);
        $fit = function (string $text, int $size, string $font, int $maxWidth) {
            while ($size > 8) {
                $box = imagettfbbox($size, 0, $font, $text);
                if ($box[2] - $box[0] <= $maxWidth) {
                    break;
                }
                $size--;
            }
            return $size;
        };

        // phone shell
        $px = 150;
        $py = 30;
        $pw = 500;
        $ph = 940;
        $rr($px, $py, $px + $pw, $py + $ph, 60, $c(20, 20, 20));
        $rr($px + 18, $py + 18, $px + $pw - 18, $py + $ph - 18, 44, $white);
        $rr($px + 180, $py + 18, $px + $pw - 180, $py + 50, 16, $c(20, 20, 20));

        $sx = $px + 40;
        $sw = $pw - 80;

        // header with the site logo
        $y = $py + 100;
        $t(22, $bold, $sx, $y, $dark, '←');
        $logo = @imagecreatefrompng(public_path('assets/images/logo/logo.png'));
        if ($logo) {
            $lw = imagesx($logo);
            $lh = imagesy($logo);
            $nw = 180;
            $nh = (int) ($lh * $nw / $lw);
            imagecopyresampled($im, $logo, (int) ($sx + $sw / 2 - $nw / 2), $y - $nh + 10, 0, 0, $nw, $nh, $lw, $lh);
            imagedestroy($logo);
        }
        imagesetthickness($im, 3);
        imageellipse($im, $sx + $sw - 14, $y - 12, 20, 20, $dark);
        imageline($im, $sx + $sw - 7, $y - 5, $sx + $sw, $y + 2, $dark);
        imagesetthickness($im, 1);

        $photos = array_values(array_filter((array) $listing->photos, fn ($p) => is_file(public_path(self::PHOTO_DIR . '/' . $p))));

        // cover photo
        $coverY = $y + 30;
        $coverH = 190;
        if ($photos && self::coverInto($im, public_path(self::PHOTO_DIR . '/' . $photos[0]), $sx, $coverY, $sw, $coverH)) {
            // drawn
        } else {
            for ($i = 0; $i < $coverH; $i++) {
                imageline($im, $sx, $coverY + $i, $sx + $sw, $coverY + $i, $c(60 + (int) ($i / 3), 70, 110));
            }
        }

        // name + rating
        $y = $coverY + $coverH + 40;
        $name = (string) $listing->name;
        $t($fit($name, 19, $bold, $sw), $bold, $sx, $y, $dark, $name);

        $avg = round((float) $listing->ratings()->avg('ratings'), 1) ?: 5.0;
        $count = $listing->ratings()->count();
        $rr($sx, $y + 14, $sx + 58, $y + 44, 5, $green);
        $t(14, $bold, $sx + 8, $y + 37, $white, number_format($avg, 1) . '★');
        $t(11, $regular, $sx + 70, $y + 36, $grey, $count . ' Rating' . ($count == 1 ? '' : 's'));

        // the badge this package adds
        $badgeY = $y + 62;
        if (in_array($variant, ['verified', 'trusted'], true)) {
            $cardW = $variant === 'trusted' ? 430 : 300;
            for ($s = 12; $s > 0; $s -= 2) {
                $rr($px - 10 - $s, $badgeY - $s + 5, $px + $cardW + $s, $badgeY + 70 + $s + 5, 16 + $s, $c(0, 0, 0, 125));
            }
            $rr($px - 10, $badgeY, $px + $cardW, $badgeY + 70, 14, $white);
            imagesetthickness($im, 6);
            imageline($im, $px + 30, $badgeY + 36, $px + 42, $badgeY + 50, $blue);
            imageline($im, $px + 42, $badgeY + 50, $px + 62, $badgeY + 20, $blue);
            imagesetthickness($im, 1);
            $t(22, $bold, $px + 72, $badgeY + 47, $blue, 'SB Verified');

            if ($variant === 'trusted') {
                $gx = $px + 272;
                $rr($gx, $badgeY + 18, $gx + 130, $badgeY + 54, 6, $gold);
                imagefilledpolygon($im, [$gx + 14, $badgeY + 24, $gx + 36, $badgeY + 24, $gx + 36, $badgeY + 38, $gx + 25, $badgeY + 48, $gx + 14, $badgeY + 38], $white);
                $t(16, $bold, $gx + 46, $badgeY + 44, $c(60, 40, 0), 'Trust');
            }
        }

        if ($variant === 'top') {
            $rr($sx, $badgeY + 4, $sx + 150, $badgeY + 36, 5, $orange);
            $t(12, $bold, $sx + 18, $badgeY + 27, $white, 'TOP 1 · AD');
        }

        if ($variant === 'banner') {
            $bx1 = $px - 10;
            $bx2 = $px + $pw + 10;
            $by1 = $badgeY + 6;
            $by2 = $by1 + 110;
            for ($s = 14; $s > 0; $s -= 2) {
                $rr($bx1 - $s, $by1 - $s + 5, $bx2 + $s, $by2 + $s + 5, 14 + $s, $c(0, 0, 0, 125));
            }
            imagefilledrectangle($im, $bx1, $by1, $bx2, $by2, $c(20, 20, 20));

            if ($photos) {
                self::coverInto($im, public_path(self::PHOTO_DIR . '/' . $photos[0]), $bx1, $by1, 170, $by2 - $by1);
            }
            imagefilledpolygon($im, [$bx1 + 170, $by1, $bx1 + 520, $by1, $bx1 + 490, $by2, $bx1 + 150, $by2], $gold);
            $t($fit(mb_strtoupper($name), 17, $bold, 300), $bold, $bx1 + 200, $by1 + 48, $c(20, 20, 20), mb_strtoupper($name));
            $t(12, $bold, $bx1 + 200, $by1 + 76, $c(206, 17, 38), 'Advertise Here');
            if ($listing->mobile) {
                $t(11, $bold, $bx1 + 560, $by1 + 62, $white, 'Call: ' . $listing->mobile);
            }
        }

        // action buttons
        $y = $badgeY + ($variant === 'banner' ? 140 : 110);
        $bx = $sx;
        foreach ([['Call', true], ['Chat', false], ['Direction', false]] as [$label, $filled]) {
            $w = $label === 'Direction' ? 120 : 90;
            $rr($bx, $y, $bx + $w, $y + 34, 5, $filled ? $blue : $dark);
            if (!$filled) {
                $rr($bx + 2, $y + 2, $bx + $w - 2, $y + 32, 4, $white);
            }
            $t(13, $bold, $bx + 16, $y + 23, $filled ? $white : $dark, $label);
            $bx += $w + 10;
        }

        // tabs
        $y += 72;
        $tx = $sx;
        foreach (['Overview', 'Services', 'Reviews', 'Photos'] as $i => $tab) {
            $t(13, $i ? $regular : $bold, $tx, $y, $i ? $grey : $dark, $tab);
            if (!$i) {
                imagefilledrectangle($im, $tx, $y + 12, $tx + 80, $y + 15, $dark);
            }
            $tx += 100;
        }
        imageline($im, $px + 18, $y + 16, $px + $pw - 18, $y + 16, $lg);

        // address
        $y += 55;
        $t(15, $bold, $sx, $y, $dark, 'Address');
        $address = trim(($listing->area ? $listing->area . ', ' : '') . ($listing->city ?? '') . ($listing->pincode ? ' - ' . $listing->pincode : ''), ', ');
        $t($fit($address, 12, $regular, $sw), $regular, $sx, $y + 28, $c(60, 60, 60), $address);
        $t(12, $bold, $sx, $y + 56, $blue, '➤ Direction      Copy');

        // photos row
        $y += 100;
        $t(15, $bold, $sx, $y, $dark, 'Photos');
        $tileX = $sx;
        foreach (array_slice($photos, 0, 3) as $photo) {
            if (!self::coverInto($im, public_path(self::PHOTO_DIR . '/' . $photo), $tileX, $y + 16, 100, 100)) {
                $rr($tileX, $y + 16, $tileX + 100, $y + 116, 10, $c(230, 230, 230));
            }
            $tileX += 115;
        }
        if (!$photos) {
            $rr($sx, $y + 16, $sx + 100, $y + 116, 10, $c(230, 220, 225));
            $rr($sx + 115, $y + 16, $sx + 215, $y + 116, 10, $c(215, 225, 235));
        }

        $ok = @imagewebp($im, $dest, 88);
        imagedestroy($im);

        return $ok;
    }

    /** A certificate preview in the business's own name. */
    private static function drawCertificate(Listing $listing, string $dest): bool
    {
        $sample = new \App\Models\ListingSalesPackage([
            'listing_id' => $listing->id,
            'expires_at' => now()->addYear(),
        ]);
        $sample->expires_at = now()->addYear();

        $file = SalesPerks::drawCertificate($listing, $sample);

        if (!$file) {
            return false;
        }

        $source = public_path(SalesPerks::DIR . '/' . $file);
        $ok = @copy($source, $dest);
        @unlink($source);

        return (bool) $ok;
    }
}
