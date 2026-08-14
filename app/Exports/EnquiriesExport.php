<?php

namespace App\Exports;

use App\Exports\Sheets\ReportSheet;
use App\Models\Enquiry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EnquiriesExport implements WithMultipleSheets
{
    protected ?string $month;

    public function __construct(?string $month = null)
    {
        $this->month = $month;
    }

    public function sheets(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Listing-wise total enquiries
        |--------------------------------------------------------------------------
        */

        $listingTotals = $this->baseQuery()
            ->selectRaw('listing_id, COUNT(*) AS total')
            ->groupBy('listing_id')
            ->pluck('total', 'listing_id');

        /*
        |--------------------------------------------------------------------------
        | Location-wise total enquiries
        |--------------------------------------------------------------------------
        */

        $locationTotals = $this->baseQuery()
            ->selectRaw('location_id, COUNT(*) AS total')
            ->groupBy('location_id')
            ->pluck('total', 'location_id');

        /*
        |--------------------------------------------------------------------------
        | Category-wise total enquiries
        |--------------------------------------------------------------------------
        */

        $categoryTotals = $this->baseQuery()
            ->selectRaw('category_id, COUNT(*) AS total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        /*
        |--------------------------------------------------------------------------
        | Enquiry details
        |--------------------------------------------------------------------------
        |
        | Group ke records saath mein rahenge, taaki total sirf pehli row
        | mein show ho.
        |
        */

        $enquiries = $this->baseQuery()
            ->with([
                'listing',
                'location',
                'category',
            ])
            ->orderBy('listing_id')
            ->orderBy('location_id')
            ->orderBy('category_id')
            ->orderByDesc('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Trackers: total ek baar dikhane ke liye
        |--------------------------------------------------------------------------
        */

        $shownListings = [];
        $shownLocations = [];
        $shownCategories = [];

        $detailData = $enquiries
            ->values()
            ->map(function ($enquiry, $index) use (
                $listingTotals,
                $locationTotals,
                $categoryTotals,
                &$shownListings,
                &$shownLocations,
                &$shownCategories
            ) {
                $listingName = optional($enquiry->listing)->name ?: 'N/A';
                $locationName = optional($enquiry->location)->name ?: 'N/A';
                $categoryName = optional($enquiry->category)->name ?: 'N/A';

                /*
                 * ID na ho to N/A ko ek common key maana jayega.
                 */
                $listingKey = $enquiry->listing_id
                    ? 'listing-' . $enquiry->listing_id
                    : 'listing-na';

                $locationKey = $enquiry->location_id
                    ? 'location-' . $enquiry->location_id
                    : 'location-na';

                $categoryKey = $enquiry->category_id
                    ? 'category-' . $enquiry->category_id
                    : 'category-na';

                /*
                 * Listing total sirf listing ki pehli row mein.
                 */
                $showListingTotal = !isset(
                    $shownListings[$listingKey]
                );

                if ($showListingTotal) {
                    $shownListings[$listingKey] = true;
                }

                /*
                 * Location total sirf location ki pehli row mein.
                 */
                $showLocationTotal = !isset(
                    $shownLocations[$locationKey]
                );

                if ($showLocationTotal) {
                    $shownLocations[$locationKey] = true;
                }

                /*
                 * Category total sirf category ki pehli row mein.
                 */
                $showCategoryTotal = !isset(
                    $shownCategories[$categoryKey]
                );

                if ($showCategoryTotal) {
                    $shownCategories[$categoryKey] = true;
                }

                return [
                    $index + 1,

                    $enquiry->name ?: '-',
                    $enquiry->phone ?: '-',

                    $listingName,

                    $showListingTotal
                        ? (int) (
                            $listingTotals[$enquiry->listing_id] ?? 0
                        )
                        : '',

                    $locationName,

                    $showLocationTotal
                        ? (int) (
                            $locationTotals[$enquiry->location_id] ?? 0
                        )
                        : '',

                    $categoryName,

                    $showCategoryTotal
                        ? (int) (
                            $categoryTotals[$enquiry->category_id] ?? 0
                        )
                        : '',

                    $enquiry->created_at
                        ? $enquiry->created_at
                            ->copy()
                            ->timezone('Asia/Kolkata')
                            ->format('d M Y, h:i A')
                        : 'N/A',
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Listing-wise summary sheet
        |--------------------------------------------------------------------------
        */

        $listingWise = $this->baseQuery()
            ->leftJoin(
                'listings',
                'enquiries.listing_id',
                '=',
                'listings.id'
            )
            ->selectRaw("
                COALESCE(NULLIF(listings.name, ''), 'N/A')
                AS item_name
            ")
            ->selectRaw('COUNT(enquiries.id) AS total_enquiries')
            ->groupBy(
                'enquiries.listing_id',
                'listings.name'
            )
            ->orderByDesc('total_enquiries')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row->item_name,
                    (int) $row->total_enquiries,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Category-wise summary sheet
        |--------------------------------------------------------------------------
        */

        $categoryWise = $this->baseQuery()
            ->leftJoin(
                'categories',
                'enquiries.category_id',
                '=',
                'categories.id'
            )
            ->selectRaw("
                COALESCE(NULLIF(categories.name, ''), 'N/A')
                AS item_name
            ")
            ->selectRaw('COUNT(enquiries.id) AS total_enquiries')
            ->groupBy(
                'enquiries.category_id',
                'categories.name'
            )
            ->orderByDesc('total_enquiries')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row->item_name,
                    (int) $row->total_enquiries,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Location-wise summary sheet
        |--------------------------------------------------------------------------
        */

        $locationWise = $this->baseQuery()
            ->leftJoin(
                'locations',
                'enquiries.location_id',
                '=',
                'locations.id'
            )
            ->selectRaw("
                COALESCE(NULLIF(locations.name, ''), 'N/A')
                AS item_name
            ")
            ->selectRaw('COUNT(enquiries.id) AS total_enquiries')
            ->groupBy(
                'enquiries.location_id',
                'locations.name'
            )
            ->orderByDesc('total_enquiries')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row->item_name,
                    (int) $row->total_enquiries,
                ];
            });

        return [
            new ReportSheet(
                $detailData,
                [
                    '#',
                    'Name',
                    'Phone',
                    'Listing',
                    'Listing Total Enquiries',
                    'Location',
                    'Location Total Enquiries',
                    'Category',
                    'Category Total Enquiries',
                    'Date',
                ],
                'Enquiries Detail'
            ),

            new ReportSheet(
                $listingWise,
                [
                    '#',
                    'Listing',
                    'Total Enquiries',
                ],
                'Listing Wise Enquiries'
            ),

            new ReportSheet(
                $categoryWise,
                [
                    '#',
                    'Category',
                    'Total Enquiries',
                ],
                'Category Wise Enquiries'
            ),

            new ReportSheet(
                $locationWise,
                [
                    '#',
                    'Location',
                    'Total Enquiries',
                ],
                'Location Wise Enquiries'
            ),
        ];
    }

    private function baseQuery(): Builder
    {
        $query = Enquiry::query();

        if (!empty($this->month)) {
            $startDate = Carbon::createFromFormat(
                'Y-m',
                $this->month,
                'Asia/Kolkata'
            )
                ->startOfMonth()
                ->utc();

            $endDate = Carbon::createFromFormat(
                'Y-m',
                $this->month,
                'Asia/Kolkata'
            )
                ->endOfMonth()
                ->utc();

            $query->whereBetween('enquiries.created_at', [
                $startDate,
                $endDate,
            ]);
        }

        return $query;
    }
}