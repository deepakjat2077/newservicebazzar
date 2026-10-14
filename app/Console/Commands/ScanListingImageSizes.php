<?php

namespace App\Console\Commands;

use App\Support\ListingImageSizes;
use Illuminate\Console\Command;

class ScanListingImageSizes extends Command
{
    protected $signature = 'listings:scan-image-sizes';

    protected $description = 'Read and store the pixel size of every listing image not yet measured (for the small-images report)';

    public function handle(): int
    {
        $read = ListingImageSizes::sync();
        $this->info("Measured {$read} new image(s).");

        return self::SUCCESS;
    }
}
