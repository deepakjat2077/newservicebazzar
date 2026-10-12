<?php

namespace App\Exports;

use App\Exports\Sheets\ReportSheet;
use App\Models\ListingClick;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ListingClicksExport implements WithMultipleSheets
{
    protected ?Carbon $startDate;
    protected ?Carbon $endDate;
    protected ?string $type;

    /**
     * $startDate/$endDate both null means "no date filter, export
     * everything" — the caller (EnquiryController::resolveClickDateRange)
     * already resolved whichever of Select Month / Date Range the admin
     * picked into this one UTC range, so this class only ever needs to know
     * the two ends. $type (call/whatsapp/enquiry) mirrors the page's own
     * All/Call/WhatsApp/Enquiry switch — null exports all three.
     */
    public function __construct(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $type = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
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

                    $click->owner_name ?: '',

                    $click->owner_number ?: '',

                    $click->clicked_number ?: '',

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
            ->selectRaw("MAX(NULLIF(owner_number, '')) AS owner_no")
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
                    (string) ($row->owner_no ?? ''),
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

        /*
        |--------------------------------------------------------------------------
        | Number-wise taps — how many times each number was tapped to call or
        | to WhatsApp (the number actually dialled, else the owner's number)
        |--------------------------------------------------------------------------
        */

        $numberWise = $this->baseQuery()
            ->whereIn('type', ['call', 'whatsapp'])
            ->selectRaw("COALESCE(NULLIF(clicked_number, ''), NULLIF(owner_number, ''), 'N/A') AS num")
            ->selectRaw("MAX(COALESCE(NULLIF(listing_name, ''), 'N/A')) AS listing")
            ->selectRaw("MAX(COALESCE(owner_name, '')) AS owner")
            ->selectRaw("SUM(CASE WHEN type = 'call' THEN 1 ELSE 0 END) AS calls")
            ->selectRaw("SUM(CASE WHEN type = 'whatsapp' THEN 1 ELSE 0 END) AS whatsapps")
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('num')
            ->orderByDesc('total')
            ->get()
            ->values()
            ->map(fn ($row, $index) => [
                $index + 1,
                (string) $row->num,
                $row->listing,
                $row->owner,
                (int) $row->calls,
                (int) $row->whatsapps,
                (int) $row->total,
            ]);

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
                    'Owner Name',
                    'Owner Number',
                    'Clicked Number',
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
                    'Owner Number',
                    'Total Clicks',
                    'Call Clicks',
                    'WhatsApp Clicks',
                    'Enquiry Clicks',
                ],
                'Listing Wise'
            ),

            new ReportSheet(
                $numberWise,
                [
                    '#',
                    'Number',
                    'Listing',
                    'Owner Name',
                    'Call Taps',
                    'WhatsApp Taps',
                    'Total Taps',
                ],
                'Number Wise'
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

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                $this->startDate,
                $this->endDate,
            ]);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        return $query;
    }
}