<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports “GD Library” and “Imagick” to process images
    | internally. Depending on your PHP setup, you can choose one of them.
    |
    | Included options:
    |   - \Intervention\Image\Drivers\Gd\Driver::class
    |   - \Intervention\Image\Drivers\Imagick\Driver::class
    |
    */

    // GD's WebP encoder is measurably slower than Imagick's for the same
    // quality target — benchmarked on this server against a real 5712x4284
    // phone photo (decode + scaleDown(2000) + WebP@82, matching
    // App\Support\Images\WebpImage exactly): GD averaged 4.77s, Imagick
    // 1.92s, roughly 2.5x. Every image an admin uploads on "Add Listing"
    // (thumb + each of the gallery photos, one at a time — see
    // WebpImage::store()) runs this exact conversion synchronously before
    // the save can complete, so on a listing with several large photos this
    // was a large share of "the page just sits there." Imagick is
    // confirmed present and enabled on both app servers (php -m).
    'driver' => \Intervention\Image\Drivers\Imagick\Driver::class,

    /*
    |--------------------------------------------------------------------------
    | Configuration Options
    |--------------------------------------------------------------------------
    |
    | These options control the behavior of Intervention Image.
    |
    | - "autoOrientation" controls whether an imported image should be
    |    automatically rotated according to any existing Exif data.
    |
    | - "decodeAnimation" decides whether a possibly animated image is
    |    decoded as such or whether the animation is discarded.
    |
    | - "blendingColor" Defines the default blending color.
    |
    | - "strip" controls if meta data like exif tags should be removed when
    |    encoding images.
    */

    'options' => [
        'autoOrientation' => true,
        'decodeAnimation' => true,
        'blendingColor' => 'ffffff',
        'strip' => false,
    ]
];
