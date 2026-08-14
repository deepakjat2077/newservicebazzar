<?php

namespace App\Exports;

use App\Exports\Sheets\ReportSheet;
use App\Models\ListingClick;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ListingClicksExport implements WithMultipleSheets
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
        | Listing-wise total clicks
        |--------------------------------------------------------------------------
        */

        $listingTotals = $this->baseQuery()
            ->selectRaw("
                COALESCE(NULLIF(listing_name, ''), 'N/A') AS item_name
            ")
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('listing_name')
            ->pluck('total', 'item_name');

        /*
        |--------------------------------------------------------------------------
        | Location-wise total clicks
        |--------------------------------------------------------------------------
        */

        $locationTotals = $this->baseQuery()
            ->selectRaw("
                COALESCE(NULLIF(location_name, ''), 'N/A') AS item_name
            ")
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('location_name')
            ->pluck('total', 'item_name');

        /*
        |--------------------------------------------------------------------------
        | Category-wise total clicks
        |--------------------------------------------------------------------------
        */

        $categoryTotals = $this->baseQuery()
            ->selectRaw("
                COALESCE(NULLIF(category_name, ''), 'N/A') AS item_name
            ")
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('category_name')
            ->pluck('total', 'item_name');

        /*
        |--------------------------------------------------------------------------
        | Listing ke har button/type ka total
        |--------------------------------------------------------------------------
        |
        | Example:
        | Professional Packers + Call = 10
        | Professional Packers + Whatsapp = 2
        |
        */

        $typeTotals = $this->baseQuery()
            ->selectRaw("
                COALESCE(NULLIF(listing_name, ''), 'N/A') AS listing_key
            ")
            ->selectRaw("
                COALESCE(NULLIF(type, ''), 'N/A') AS type_key
            ")
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('listing_name', 'type')
            ->get()
            ->mapWithKeys(function ($row) {
                $key = $row->listing_key . '|' . strtolower($row->type_key);

                return [
                    $key => (int) $row->total,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Click detail records
        |--------------------------------------------------------------------------
        |
        | Grouping ke liye listing, location, category aur type ke order mein.
        |
        */

        $clicks = $this->baseQuery()
            ->orderBy('listing_name')
            ->orderBy('location_name')
            ->orderBy('category_name')
            ->orderBy('type')
            ->orderByDesc('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Duplicate totals ko blank karne ke liye trackers
        |--------------------------------------------------------------------------
        */

        $shownListings = [];
        $shownLocations = [];
        $shownCategories = [];
        $shownTypes = [];

        $detailData = $clicks
            ->values()
            ->map(function ($click, $index) use (
                $listingTotals,
                $locationTotals,
                $categoryTotals,
                $typeTotals,
                &$shownListings,
                &$shownLocations,
                &$shownCategories,
                &$shownTypes
            ) {
                $listingName = $click->listing_name ?: 'N/A';
                $locationName = $click->location_name ?: 'N/A';
                $categoryName = $click->category_name ?: 'N/A';
                $typeName = $click->type ?: 'N/A';

                /*
                 * Listing total sirf listing ki pehli row mein.
                 */
                $showListingTotal = !isset(
                    $shownListings[$listingName]
                );

                if ($showListingTotal) {
                    $shownListings[$listingName] = true;
                }

                /*
                 * Location total sirf location ki pehli row mein.
                 */
                $showLocationTotal = !isset(
                    $shownLocations[$locationName]
                );

                if ($showLocationTotal) {
                    $shownLocations[$locationName] = true;
                }

                /*
                 * Category total sirf category ki pehli row mein.
                 */
                $showCategoryTotal = !isset(
                    $shownCategories[$categoryName]
                );

                if ($showCategoryTotal) {
                    $shownCategories[$categoryName] = true;
                }

                /*
                 * Type total listing + type combination ki
                 * pehli row mein.
                 */
                $typeKey = $listingName . '|' . strtolower($typeName);

                $showTypeTotal = !isset(
                    $shownTypes[$typeKey]
                );

                if ($showTypeTotal) {
                    $shownTypes[$typeKey] = true;
                }

                return [
                    $index + 1,

                    $listingName,

                    $showListingTotal
                        ? (int) ($listingTotals[$listingName] ?? 0)
                        : '',

                    $locationName,

                    $showLocationTotal
                        ? (int) ($locationTotals[$locationName] ?? 0)
                        : '',

                    $categoryName,

                    $showCategoryTotal
                        ? (int) ($categoryTotals[$categoryName] ?? 0)
                        : '',

                    ucfirst($typeName),

                    $showTypeTotal
                        ? (int) ($typeTotals[$typeKey] ?? 0)
                        : '',

                    $click->created_at
                        ? $click->created_at
                            ->copy()
                            ->timezone('Asia/Kolkata')
                            ->format('d M Y, h:i A')
                        : 'N/A',
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Overall click totals
        |--------------------------------------------------------------------------
        */

        $totalData = collect([
            [
                $this->baseQuery()->count(),

                $this->baseQuery()
                    ->where('type', 'call')
                    ->count(),

                $this->baseQuery()
                    ->where('type', 'whatsapp')
                    ->count(),

                $this->baseQuery()
                    ->where('type', 'enquiry')
                    ->count(),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Listing-wise summary
        |--------------------------------------------------------------------------
        */

        $listingWise = $this->baseQuery()
            ->selectRaw("
                COALESCE(NULLIF(listing_name, ''), 'N/A') AS item_name
            ")
            ->selectRaw('COUNT(*) AS total_clicks')
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'call'
                    THEN 1 ELSE 0 END
                ) AS total_calls
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'whatsapp'
                    THEN 1 ELSE 0 END
                ) AS total_whatsapp
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'enquiry'
                    THEN 1 ELSE 0 END
                ) AS total_enquiries
            ")
            ->groupBy('listing_name')
            ->orderByDesc('total_clicks')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row->item_name,
                    (int) $row->total_clicks,
                    (int) $row->total_calls,
                    (int) $row->total_whatsapp,
                    (int) $row->total_enquiries,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Category-wise summary
        |--------------------------------------------------------------------------
        */

        $categoryWise = $this->baseQuery()
            ->selectRaw("
                COALESCE(NULLIF(category_name, ''), 'N/A') AS item_name
            ")
            ->selectRaw('COUNT(*) AS total_clicks')
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'call'
                    THEN 1 ELSE 0 END
                ) AS total_calls
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'whatsapp'
                    THEN 1 ELSE 0 END
                ) AS total_whatsapp
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'enquiry'
                    THEN 1 ELSE 0 END
                ) AS total_enquiries
            ")
            ->groupBy('category_name')
            ->orderByDesc('total_clicks')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row->item_name,
                    (int) $row->total_clicks,
                    (int) $row->total_calls,
                    (int) $row->total_whatsapp,
                    (int) $row->total_enquiries,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Location-wise summary
        |--------------------------------------------------------------------------
        */

        $locationWise = $this->baseQuery()
            ->selectRaw("
                COALESCE(NULLIF(location_name, ''), 'N/A') AS item_name
            ")
            ->selectRaw('COUNT(*) AS total_clicks')
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'call'
                    THEN 1 ELSE 0 END
                ) AS total_calls
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'whatsapp'
                    THEN 1 ELSE 0 END
                ) AS total_whatsapp
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN type = 'enquiry'
                    THEN 1 ELSE 0 END
                ) AS total_enquiries
            ")
            ->groupBy('location_name')
            ->orderByDesc('total_clicks')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row->item_name,
                    (int) $row->total_clicks,
                    (int) $row->total_calls,
                    (int) $row->total_whatsapp,
                    (int) $row->total_enquiries,
                ];
            });

        return [
            new ReportSheet(
                $detailData,
                [
                    '#',
                    'Listing',
                    'Listing Total Clicks',
                    'Location',
                    'Location Total Clicks',
                    'Category',
                    'Category Total Clicks',
                    'Type',
                    'Type Total Clicks',
                    'Date',
                ],
                'Click Details'
            ),

            new ReportSheet(
                $totalData,
                [
                    'Total Clicks',
                    'Total Calls',
                    'Total WhatsApp',
                    'Total Enquiry Clicks',
                ],
                'Click Totals'
            ),

            new ReportSheet(
                $listingWise,
                [
                    '#',
                    'Listing',
                    'Total Clicks',
                    'Call Clicks',
                    'WhatsApp Clicks',
                    'Enquiry Clicks',
                ],
                'Listing Wise'
            ),

            new ReportSheet(
                $categoryWise,
                [
                    '#',
                    'Category',
                    'Total Clicks',
                    'Call Clicks',
                    'WhatsApp Clicks',
                    'Enquiry Clicks',
                ],
                'Category Wise'
            ),

            new ReportSheet(
                $locationWise,
                [
                    '#',
                    'Location',
                    'Total Clicks',
                    'Call Clicks',
                    'WhatsApp Clicks',
                    'Enquiry Clicks',
                ],
                'Location Wise'
            ),
        ];
    }

    private function baseQuery(): Builder
    {
        $query = ListingClick::query();

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

            $query->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        return $query;
    }
}