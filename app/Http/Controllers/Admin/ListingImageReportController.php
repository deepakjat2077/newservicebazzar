<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SmallListingImagesExport;
use App\Http\Controllers\Controller;
use App\Support\ListingImageSizes;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Admin > Listing > "Small images" Excel: every listing with a main or gallery
 * photo whose shorter side is under the chosen size (default 500 px).
 */
class ListingImageReportController extends Controller
{
    public function export(Request $request)
    {
        $minSide = in_array((int) $request->min, [300, 500, 700], true) ? (int) $request->min : 500;

        // Pick up images uploaded since the nightly scan, without risking the
        // request timeout (the full first scan is done by the artisan command).
        ListingImageSizes::sync(15);

        $rows = ListingImageSizes::report($minSide);

        return Excel::download(
            new SmallListingImagesExport($rows, $minSide),
            'listings-small-images-under-' . $minSide . 'px-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
