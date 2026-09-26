<?php

namespace App\Http\Controllers;

use App\Models\ListingAdBanner;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * The buyer's own screen for a banner they paid for: they fill in the text and
 * (optionally) a photo, and the creative is generated right there. The banner
 * runs from the purchase date for exactly the days the plan was sold for.
 */
class AdBannerController extends Controller
{
    private const DIR = 'assets/images/ad_banners';

    protected $theme;

    public function __construct()
    {
        $this->theme = 'themes.' . (Setting::first()->theme ?? 'servicebazzar') . '.';
    }

    public function edit($id)
    {
        $banner = $this->ownedBanner($id);

        return view($this->theme . 'user.listing.ad-banner', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = $this->ownedBanner($id);

        $data = $request->validate([
            'title'    => 'required|string|max:60',
            'subtitle' => 'nullable|string|max:80',
            'phone'    => 'nullable|string|max:20',
            'photo'    => 'nullable|image|max:10240',
        ]);

        $banner->fill([
            'title'    => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'phone'    => $data['phone'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            $dir = public_path(self::DIR);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $file = 'photo_' . uniqid() . '.webp';
            $this->toWebp($request->file('photo')->getRealPath(), $dir . '/' . $file, 900);
            $this->removeFile($banner->photo);
            $banner->photo = $file;
        }

        $this->generate($banner);

        // Live only while the purchase is still inside its paid window.
        if ($banner->status === ListingAdBanner::PENDING && $banner->expires_at && $banner->expires_at->isFuture()) {
            $banner->status = ListingAdBanner::LIVE;
        }

        $banner->save();

        return back()->with('success', 'Your banner is ready and running till ' . $banner->expires_at->format('d M Y, h:i A') . '.');
    }

    private function ownedBanner($id): ListingAdBanner
    {
        return ListingAdBanner::whereHas('listing', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['listing', 'package'])
            ->findOrFail($id);
    }

    /**
     * Draws the banner at the two sizes the site's own category banners use:
     * 1366x113 for desktop and 670x176 for mobile.
     */
    private function generate(ListingAdBanner $banner): void
    {
        $stamp = time();

        $desktop = 'banner_' . $banner->id . '_' . $stamp . '.webp';
        $mobile = 'banner_' . $banner->id . '_' . $stamp . '_m.webp';

        $this->draw($banner, public_path(self::DIR) . '/' . $desktop, 1366, 113);
        $this->draw($banner, public_path(self::DIR) . '/' . $mobile, 670, 176);

        $this->removeFile($banner->image);
        $this->removeFile($banner->image_mobile);

        $banner->image = $desktop;
        $banner->image_mobile = $mobile;
    }

    /** One banner image: photo on the left, yellow headline panel, city on the right. */
    private function draw(ListingAdBanner $banner, string $dest, int $W, int $H): void
    {
        $im = imagecreatetruecolor($W, $H);

        $col = fn ($r, $g, $b) => imagecolorallocate($im, $r, $g, $b);
        $black = $col(20, 20, 20);
        $white = $col(255, 255, 255);
        $yellow = $col(250, 200, 30);
        $red = $col(206, 17, 38);
        $grey = $col(200, 200, 200);

        imagefill($im, 0, 0, $black);

        $bold = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $serif = '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf';
        $regular = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

        // Proportions of the whole strip, so both sizes look the same.
        $photoW = (int) round($W * 0.22);
        $panelEnd = (int) round($W * 0.74);
        $skew = (int) round($W * 0.035);
        $textX = $photoW + (int) round($W * 0.045);
        $rightX = $panelEnd + $skew + (int) round($W * 0.025);

        // left photo (theirs, or a neutral block)
        $photoPath = $banner->photo ? public_path(self::DIR . '/' . $banner->photo) : null;
        $photo = $photoPath && is_file($photoPath) ? @imagecreatefromwebp($photoPath) : null;
        if ($photo) {
            $pw = imagesx($photo);
            $ph = imagesy($photo);
            $scale = max(($photoW + $skew) / $pw, $H / $ph);
            $nw = (int) round($pw * $scale);
            $nh = (int) round($ph * $scale);
            imagecopyresampled($im, $photo, 0, (int) (($H - $nh) / 2), 0, 0, $nw, $nh, $pw, $ph);
            imagedestroy($photo);
        } else {
            for ($i = 0; $i < $photoW + $skew; $i++) {
                imageline($im, $i, 0, $i, $H, $col(60 + (int) ($i / 6), 45 + (int) ($i / 8), 35));
            }
        }

        // yellow headline panel, angled like a printed strip
        imagefilledpolygon($im, [
            $photoW + $skew, 0, $panelEnd + $skew, 0, $panelEnd, $H, $photoW, $H,
        ], $yellow);

        $fit = function (string $text, int $size, string $font, int $maxWidth, int $min = 7) {
            while ($size > $min) {
                $box = imagettfbbox($size, 0, $font, $text);
                if ($box[2] - $box[0] <= $maxWidth) {
                    break;
                }
                $size--;
            }
            return $size;
        };

        $textW = $panelEnd - $textX - (int) round($W * 0.02);

        // Three stacked lines inside the panel, scaled to the strip height.
        $title = mb_strtoupper((string) $banner->title);
        $tSize = $fit($title, (int) round($H * 0.26), $serif, $textW);
        imagettftext($im, $tSize, 0, $textX, (int) round($H * 0.38), $black, $serif, $title);

        if ($banner->subtitle) {
            $sSize = $fit($banner->subtitle, (int) round($H * 0.17), $bold, $textW);
            imagettftext($im, $sSize, 0, $textX, (int) round($H * 0.64), $red, $bold, $banner->subtitle);
        }

        if ($banner->phone) {
            $phone = 'Call: ' . $banner->phone;
            $pSize = $fit($phone, (int) round($H * 0.16), $bold, $textW);
            imagettftext($im, $pSize, 0, $textX, (int) round($H * 0.9), $black, $bold, $phone);
        }

        // right side: where the ad runs
        $place = trim(($banner->area ? $banner->area . ', ' : '') . ($banner->city ?? ''));
        $rightW = $W - $rightX - (int) round($W * 0.02);
        if ($place !== '') {
            $plSize = $fit($place, (int) round($H * 0.16), $bold, $rightW);
            imagettftext($im, $plSize, 0, $rightX, (int) round($H * 0.5), $white, $bold, $place);
        }
        $note = 'on Service Bazzar';
        $nSize = $fit($note, (int) round($H * 0.12), $regular, $rightW);
        imagettftext($im, $nSize, 0, $rightX, (int) round($H * 0.78), $grey, $regular, $note);

        imagewebp($im, $dest, 90);
        imagedestroy($im);
    }

    private function removeFile(?string $file): void
    {
        if ($file && is_file($path = public_path(self::DIR . '/' . $file))) {
            @unlink($path);
        }
    }

    private function toWebp(string $src, string $dest, int $max = 1200): void
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
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagewebp($dst, $dest, 85);
        imagedestroy($img);
        imagedestroy($dst);
    }
}
