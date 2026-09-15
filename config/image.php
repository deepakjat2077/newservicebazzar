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

    // Imagick is ~2.5x faster than GD at WebP encoding (1.92s vs 4.77s,
    // benchmarked on a real 5712x4284 phone photo), which matters because
    // every gallery photo runs this conversion synchronously on upload.
    // NOTE: a brief switch back to GD was made when business-photos uploads
    // looked "corrupted", but the real cause was a forced resize(730x484)
    // that stretched images regardless of driver (fixed in
    // ListingController::addExtraImage) — Imagick itself was producing valid
    // files, so it's restored here for the speed.
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
