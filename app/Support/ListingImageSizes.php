<?php

namespace App\Support;

use App\Models\Listing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reads and remembers listing image sizes (table listing_image_sizes) and builds
 * the "listings with small images" report for the admin Excel download.
 *
 * Reading all ~44k files takes ~80 s, longer than a web request may run, so
 * listings:scan-image-sizes reads everything once and nightly; the download only
 * reads files it has not seen yet, within a time budget.
 */
class ListingImageSizes
{
    public const THUMB_DIR = 'assets/images/listing/thumb/';
    public const PHOTO_DIR = 'assets/images/listing/photos/';

    /** [kind, path under public/] for each image a listing currently uses. */
    public static function filesOf($listing): array
    {
        $files = [];
        if (!empty($listing->thumb)) {
            $files[] = ['thumb', self::THUMB_DIR . $listing->thumb];
        }

        $photos = is_array($listing->photos) ? $listing->photos : (json_decode((string) $listing->photos, true) ?: []);
        foreach ($photos as $p) {
            if (is_string($p) && $p !== '') {
                $files[] = ['photo', self::PHOTO_DIR . $p];
            }
        }

        return $files;
    }

    /**
     * Read the size of every listing image not yet in the table.
     * Stops after $seconds (0 = no limit). Returns how many files it read.
     */
    public static function sync(int $seconds = 0): int
    {
        $start = microtime(true);
        $read = 0;

        Listing::select(['id', 'thumb', 'photos'])->chunkById(500, function ($listings) use (&$read, $start, $seconds) {
            $paths = [];
            foreach ($listings as $l) {
                foreach (self::filesOf($l) as [, $path]) {
                    $paths[$path] = true;
                }
            }
            if (!$paths) {
                return true;
            }

            $known = DB::table('listing_image_sizes')->whereIn('file', array_keys($paths))->pluck('file')->flip();
            $rows = [];
            foreach (array_keys($paths) as $path) {
                if (isset($known[$path])) {
                    continue;
                }
                $size = @getimagesize(public_path($path));
                $rows[] = [
                    'file'       => $path,
                    'width'      => $size[0] ?? null,
                    'height'     => $size[1] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $read++;
                if ($seconds && microtime(true) - $start > $seconds) {
                    break;
                }
            }
            if ($rows) {
                DB::table('listing_image_sizes')->insertOrIgnore($rows);
            }

            return !($seconds && microtime(true) - $start > $seconds);
        });

        return $read;
    }

    /**
     * One row per listing that has at least one image whose shorter side is under
     * $minSide pixels, most small images first.
     */
    public static function report(int $minSide): Collection
    {
        $sizes = DB::table('listing_image_sizes')->pluck(DB::raw("CONCAT(COALESCE(width,''),'x',COALESCE(height,''))"), 'file');

        $rows = collect();
        Listing::with(['category:id,name,slug', 'location:id,name'])
            ->select(['id', 'name', 'slug', 'status', 'thumb', 'photos', 'category_id', 'location_id', 'city'])
            ->chunkById(1000, function ($listings) use ($sizes, $minSide, $rows) {
                foreach ($listings as $l) {
                    $files = self::filesOf($l);
                    $small = 0;
                    $smallThumb = false;
                    $missing = 0;
                    $smallest = null;
                    $smallestSide = PHP_INT_MAX;

                    foreach ($files as [$kind, $path]) {
                        $dim = $sizes[$path] ?? null;
                        [$w, $h] = $dim ? array_pad(explode('x', $dim), 2, '') : ['', ''];
                        if ($w === '' || $h === '') {
                            $missing++;
                            continue;
                        }
                        $side = min((int) $w, (int) $h);
                        if ($side < $smallestSide) {
                            $smallestSide = $side;
                            $smallest = $w . ' x ' . $h;
                        }
                        if ($side < $minSide) {
                            $small++;
                            $smallThumb = $smallThumb || $kind === 'thumb';
                        }
                    }

                    if ($small === 0) {
                        continue;
                    }

                    $rows->push([
                        'id'          => $l->id,
                        'name'        => $l->name,
                        'category'    => optional($l->category)->name,
                        'city'        => optional($l->location)->name ?: $l->city,
                        'status'      => (int) $l->status === 1 ? 'Approved' : ((int) $l->status === 2 ? 'Rejected' : 'Pending'),
                        'total'       => count($files),
                        'small'       => $small,
                        'small_thumb' => $smallThumb ? 'Yes' : 'No',
                        'smallest'    => $smallest,
                        'missing'     => $missing,
                        'edit_url'    => route('admin.listing.edit', $l->id),
                        'page_url'    => optional($l->category)->slug && $l->slug
                            ? url('listings/' . $l->category->slug . '/' . $l->slug) : '',
                    ]);
                }
            });

        return $rows->sortByDesc('small')->values();
    }
}
