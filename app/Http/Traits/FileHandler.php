<?php

namespace App\Http\Traits;

use App\Support\Images\WebpImage;
use Intervention\Image\Laravel\Facades\Image;
use Exception;

trait FileHandler
{
    /**
     * Extensions allowed for uploads processed as images. Enforced against the
     * client-supplied extension as defense-in-depth; the actual image content
     * is still separately decoded/validated by Intervention Image below.
     */
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Extensions that must never be written into a publicly web-served
     * directory, regardless of upload type.
     */
    private const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phar', 'pht',
        'shtml', 'htaccess', 'htm', 'html', 'js', 'mjs', 'svg', 'exe', 'sh',
        'bat', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp', 'cer', 'config', 'ini',
    ];

    /**
     * Biggest image we are willing to decode, in megapixels.
     *
     * GD holds roughly 4 bytes per pixel for as long as it is working, so a
     * 24 MP phone photo costs ~96 MB of raw pixels — and a listing form can
     * post ten of them in one request. That is what made a 3 GB memory_limit
     * look necessary; the cap is what makes 512 MB enough instead.
     *
     * Anything above this is refused with a message the uploader can act on,
     * rather than killing the whole request with an out-of-memory fatal.
     */
    private const MAX_IMAGE_MEGAPIXELS = 40;

    /** Longest edge we keep when the caller did not ask for a specific size. */
    private const MAX_IMAGE_DIMENSION = 2000;

    protected function uploadFile($type, $data)
    {
        $filename = $this->fileUploadProcess($data, $type);

        if ($type == 'image') {
            // Note the return: the name written is not the one just generated,
            // because the image is stored as WebP and the extension changes.
            // Callers save this value, so they record the file that exists.
            return WebpImage::store(
                $data['file'],
                $data['path'],
                pathinfo($filename, PATHINFO_FILENAME),
                $this->sizeFrom($data)
            );
        }

        $data['file']->move(public_path($data['path']), $filename);

        return $filename;
    }

    /**
     * Store an image, converted to WebP.
     *
     * Returns the filename it wrote — callers save that to the database, and
     * for an image it will not be the $filename they passed in.
     *
     * The one exception is a caller that named the file with its own
     * extension (SettingController writes 'logo.png', 'favicon_new.png').
     * Those names are hardcoded in the layouts, so the extension is honoured
     * as given and only the resizing applies. See WebpImage.
     */
    protected function uploadImage($data, $filename)
    {
        // Reached only from a caller that named the file itself — uploadFile()
        // never calls this, it goes straight to WebpImage with a bare basename.
        return $this->storeWithFixedName($data, $filename, $this->sizeFrom($data));
    }

    private function sizeFrom($data): ?array
    {
        if (empty($data['size'])) {
            return null;
        }

        $parts = explode('x', $data['size']);

        return [
            'width' => (int) ($parts[0] ?? 0),
            'height' => (int) ($parts[1] ?? 0),
        ];
    }

    /**
     * The logo/favicon path: resize as before, keep the caller's filename.
     */
    private function storeWithFixedName($data, $filename, ?array $size)
    {
        WebpImage::guardSize($data['file']);

        $image = Image::read($data['file']);

        try {
            if ($size && $size['width'] > 0 && $size['height'] > 0) {
                $image->resize($size['width'], $size['height']);
            } else {
                $image->scaleDown(self::MAX_IMAGE_DIMENSION, self::MAX_IMAGE_DIMENSION);
            }

            $image->save(public_path($data['path'].'/'.$filename));
        } finally {
            unset($image);
            gc_collect_cycles();
        }

        return $filename;
    }

    /**
     * Refuse an image that cannot be decoded inside the memory we have.
     *
     * getimagesize() reads the header only — a few bytes — so the dimensions
     * are known before anything is committed to memory.
     */
    private function guardImageSize($file): void
    {
        $path = is_object($file) && method_exists($file, 'getRealPath')
            ? $file->getRealPath()
            : null;

        if (!$path || !is_file($path)) {
            return; // Not something we can inspect; Intervention will judge it.
        }

        $info = @getimagesize($path);

        if ($info === false) {
            throw new Exception('That file is not a readable image.');
        }

        $megapixels = ((int) $info[0] * (int) $info[1]) / 1000000;

        if ($megapixels > self::MAX_IMAGE_MEGAPIXELS) {
            throw new Exception(sprintf(
                'Image is too large to process (%d x %d). Please upload a photo under %d megapixels.',
                (int) $info[0],
                (int) $info[1],
                self::MAX_IMAGE_MEGAPIXELS
            ));
        }
    }

    private function fileUploadProcess($data, $type = 'file')
    {
        $path = $this->createFolder(public_path($data['path']));

        if (!$path) {
            throw new Exception('Path not found');
        }

        if (!empty($data['prevFile'])) {
            $this->deleteFile(public_path($data['path'] . '/' . $data['prevFile']));
        }

        return $this->getFileName($data['file'], $type);
    }

    private function createFolder($locat)
    {
        if (!file_exists($locat)) {
            return mkdir($locat, 0755, true);
        }

        return true;
    }

    protected function deleteFile($locat)
    {
        if (file_exists($locat) && is_file($locat)) {
            @unlink($locat);
        }
    }

    private function getFileName($file, $type = 'file')
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if ($type === 'image') {
            if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
                throw new Exception('Invalid image file type.');
            }
        } elseif ($extension === '' || in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            throw new Exception('Invalid file type.');
        }

        return uniqid() . time() . '.' . $extension;
    }
}
