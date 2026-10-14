<?php

namespace App\Support\Images;

use Exception;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Every image uploaded through the admin panel is stored as WebP.
 *
 * The panel used to keep whatever the uploader happened to have — a 4 MB
 * phone JPEG, a screenshot PNG — and serve that same file to every visitor.
 * WebP is typically a quarter to a third of the size at the same visible
 * quality, and it is the one decision that applies to all of them, so it is
 * made here rather than in each of the twenty-odd places that accept a file.
 *
 * Only newly uploaded files are affected. Images already on disk keep their
 * names and extensions and are still served exactly as before — nothing is
 * rewritten or migrated, so no existing page can break.
 *
 * Two things are deliberately left alone:
 *
 *  - Animated GIFs. GD can only read the first frame, so converting one would
 *    silently turn an animation into a still. They are stored unchanged.
 *  - Uploads the caller named itself, extension included (the logo and
 *    favicon). Those filenames are hardcoded in the layouts, and renaming the
 *    file without touching the markup would just make the image disappear.
 */
class WebpImage
{
    /**
     * Visually indistinguishable from the original for photographs, and
     * roughly a third of the bytes. Worth revisiting only with real images to
     * compare against.
     */
    public const QUALITY = 82;

    /** Longest edge kept when the caller does not ask for a specific size. */
    public const MAX_DIMENSION = 2000;

    /**
     * Biggest image we are willing to decode, in megapixels.
     *
     * GD holds about 4 bytes per pixel while it works, so a 24 MP phone photo
     * costs ~96 MB of raw pixels and a listing form can post ten at once.
     * Checked from the file header, before anything is committed to memory,
     * so an oversized upload is refused with a message instead of killing the
     * request with an out-of-memory fatal.
     */
    public const MAX_MEGAPIXELS = 40;

    /**
     * Convert an upload to WebP and write it into a public directory.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $publicDir   Path under public/, e.g. 'uploads/services'.
     * @param  string|null  $basename  Filename without extension. Generated
     *                                 when omitted.
     * @param  array{width:int,height:int,fit?:string}|null  $size  Exact size; with 'fit' => 'cover' the image is centre-cropped instead of stretched,
     *                                 for callers that need fixed dimensions.
     * @return string  The stored filename, including its extension.
     */
    public static function store($file, string $publicDir, ?string $basename = null, ?array $size = null): string
    {
        $directory = public_path(trim($publicDir, '/'));

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new Exception('Could not create the upload directory.');
        }

        $basename = $basename ?: uniqid().time();

        // An animation cannot survive the conversion, so it does not get one.
        if (self::isAnimatedGif($file)) {
            $name = $basename.'.gif';
            $file->move($directory, $name);

            return $name;
        }

        self::guardSize($file);

        $image = Image::read($file);

        try {
            if ($size && ($size['width'] ?? 0) > 0 && ($size['height'] ?? 0) > 0) {
                if (($size['fit'] ?? null) === 'cover') {
                    // Fill the box without stretching: scale, then crop the centre.
                    $image->cover((int) $size['width'], (int) $size['height']);
                } else {
                    $image->resize((int) $size['width'], (int) $size['height']);
                }
            } else {
                $image->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION);
            }

            $name = $basename.'.webp';

            // Intervention picks its encoder from the extension it is given.
            $image->save($directory.'/'.$name, self::QUALITY);

            return $name;
        } finally {
            // GD releases its buffer only when the object goes. Without this,
            // a loop over ten listing photos holds every one it has processed.
            unset($image);
            gc_collect_cycles();
        }
    }

    /**
     * True for a GIF carrying more than one frame.
     *
     * Reads the file looking for a second Graphic Control Extension block
     * rather than decoding it, so the check costs a read and no memory.
     */
    public static function isAnimatedGif($file): bool
    {
        $path = is_object($file) && method_exists($file, 'getRealPath')
            ? $file->getRealPath()
            : null;

        if (!$path || !is_file($path)) {
            return false;
        }

        $handle = @fopen($path, 'rb');

        if (!$handle) {
            return false;
        }

        try {
            if (fread($handle, 3) !== 'GIF') {
                return false;
            }

            $frames = 0;
            $chunk = '';

            while (!feof($handle) && $frames < 2) {
                $chunk = substr($chunk, -20).fread($handle, 8192);
                $frames += preg_match_all('/\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)/s', $chunk);
            }

            return $frames > 1;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Refuse an image too large to decode within the memory we have.
     *
     * getimagesize() reads the header only, so the dimensions are known
     * before any pixels are loaded.
     */
    public static function guardSize($file): void
    {
        $path = is_object($file) && method_exists($file, 'getRealPath')
            ? $file->getRealPath()
            : null;

        if (!$path || !is_file($path)) {
            return; // Not something we can inspect; the decoder will judge it.
        }

        $info = @getimagesize($path);

        if ($info === false) {
            throw new Exception('That file is not a readable image.');
        }

        $megapixels = ((int) $info[0] * (int) $info[1]) / 1000000;

        if ($megapixels > self::MAX_MEGAPIXELS) {
            throw new Exception(sprintf(
                'Image is too large to process (%d x %d). Please upload a photo under %d megapixels.',
                (int) $info[0],
                (int) $info[1],
                self::MAX_MEGAPIXELS
            ));
        }
    }
}
