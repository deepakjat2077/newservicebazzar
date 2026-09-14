<?php

namespace App\Http\Controllers;

use App\Exports\EnquiriesExport;
use App\Exports\ListingClicksExport;
use App\Models\Enquiry;
use App\Models\ListingClick;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => [
                'nullable',
                Rule::in(['read', 'unread']),
            ],
            'month' => [
                'nullable',
                'date_format:Y-m',
            ],
        ]);

        $query = Enquiry::with([
            'listing',
            'location',
            'category',
        ])->latest();

        $this->applyEnquiryMonthFilter($query, $request->month);

        if ($request->status === 'read') {
            $query->where('is_read', 1);
        } elseif ($request->status === 'unread') {
            $query->where('is_read', 0);
        }

        $enquiries = $query->paginate(15);

        /*
         * Page cards mein month ke hisaab se count.
         */
        $readCountQuery = Enquiry::where('is_read', 1);
        $unreadCountQuery = Enquiry::where('is_read', 0);

        $this->applyEnquiryMonthFilter(
            $readCountQuery,
            $request->month
        );

        $this->applyEnquiryMonthFilter(
            $unreadCountQuery,
            $request->month
        );

        $readCount = $readCountQuery->count();
        $unreadCount = $unreadCountQuery->count();

        return view(
            'admin.enquiry.index',
            compact(
                'enquiries',
                'readCount',
                'unreadCount'
            )
        );
    }

public function exportEnquiries(Request $request)
{
    $validated = $request->validate([
        'month' => [
            'nullable',
            'date_format:Y-m',
        ],
    ]);

    $month = $validated['month'] ?? null;
    $fileMonth = $month ?: 'all-months';

    $fileName = 'enquiries-report-' .
        $fileMonth .
        '-' .
        now('Asia/Kolkata')->format('Ymd-His') .
        '.xlsx';

    return Excel::download(
        new EnquiriesExport($month),
        $fileName
    );
}
    public function markAsRead($id)
    {
        $enquiry = Enquiry::findOrFail($id);

        $enquiry->update([
            'is_read' => 1,
        ]);

        return back()->with(
            'success',
            'Enquiry marked as read'
        );
    }

    public function view($id)
    {
        $enquiry = Enquiry::with([
            'listing',
            'location',
            'category',
        ])->findOrFail($id);

        return view(
            'admin.enquiry.view',
            compact('enquiry')
        );
    }

    public function delete($id)
    {
        try {
            $enquiry = Enquiry::findOrFail($id);
            $enquiry->delete();

            $alert = [
                'success',
                'Enquiry deleted successfully',
            ];

            return back()->withAlert($alert);
        } catch (\Throwable $exception) {
            report($exception);

            $alert = [
                'danger',
                'Delete failed',
            ];

            return back()->withAlert($alert);
        }
    }

    public function index2(Request $request)
    {
        $request->validate([
            'type' => [
                'nullable',
                Rule::in(['call', 'whatsapp', 'enquiry']),
            ],
            'month' => [
                'nullable',
                'date_format:Y-m',
            ],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        [$startDate, $endDate] = $this->resolveClickDateRange($request);

        $query = ListingClick::query()->latest();

        $this->applyClickDateRange($query, $startDate, $endDate);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $clicks = $query->paginate(15);

        /*
         * Filtered counts, per type.
         */
        $callQuery = ListingClick::where('type', 'call');
        $whatsappQuery = ListingClick::where('type', 'whatsapp');
        $enquiryQuery = ListingClick::where('type', 'enquiry');
        $totalQuery = ListingClick::query();

        $this->applyClickDateRange($callQuery, $startDate, $endDate);
        $this->applyClickDateRange($whatsappQuery, $startDate, $endDate);
        $this->applyClickDateRange($enquiryQuery, $startDate, $endDate);
        $this->applyClickDateRange($totalQuery, $startDate, $endDate);

        $callCount = $callQuery->count();
        $whatsappCount = $whatsappQuery->count();
        $enquiryCount = $enquiryQuery->count();
        $totalClickCount = $totalQuery->count();

        return view(
            'admin.enquiry.index2',
            compact(
                'clicks',
                'callCount',
                'whatsappCount',
                'enquiryCount',
                'totalClickCount'
            )
        );
    }

public function exportClicks(Request $request)
{
    $request->validate([
        'type' => [
            'nullable',
            Rule::in(['call', 'whatsapp', 'enquiry']),
        ],
        'month' => [
            'nullable',
            'date_format:Y-m',
        ],
        'date_from' => ['nullable', 'date'],
        'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
    ]);

    [$startDate, $endDate, $fileLabel] = $this->resolveClickDateRange($request, true);

    // Export button now sits alongside the All/Call/WhatsApp/Enquiry type
    // switches, so it exports whichever one the admin currently has
    // selected rather than always exporting everything.
    $type = $request->filled('type') ? $request->type : null;
    $fileLabel = $type ? $type . '-' . $fileLabel : $fileLabel;

    $fileName = 'listing-clicks-report-' .
        $fileLabel .
        '-' .
        now('Asia/Kolkata')->format('Ymd-His') .
        '.xlsx';

    return Excel::download(
        new ListingClicksExport($startDate, $endDate, $type),
        $fileName
    );
}

/**
 * One place that decides which filter wins when more than one is filled
 * in — Date Range is the most specific, so it wins over the original Select
 * Month. Returns UTC start/end Carbons (or both null for "no filter") ready
 * for whereBetween(), plus — only when $withLabel is true — a filename-safe
 * label describing what was applied.
 *
 * A Year Range branch lived here too (year_from/year_to, winning over Month)
 * but is removed for now per admin request — see git history on this file
 * and on resources/views/admin/enquiry/index2.blade.php to restore it.
 */
private function resolveClickDateRange(Request $request, bool $withLabel = false): array
{
    $tz = 'Asia/Kolkata';

    if ($request->filled('date_from') && $request->filled('date_to')) {
        $start = Carbon::parse($request->date_from, $tz)->startOfDay()->utc();
        $end = Carbon::parse($request->date_to, $tz)->endOfDay()->utc();
        $label = $request->date_from . '_to_' . $request->date_to;
    } elseif ($request->filled('month')) {
        $start = Carbon::createFromFormat('Y-m', $request->month, $tz)->startOfMonth()->utc();
        $end = Carbon::createFromFormat('Y-m', $request->month, $tz)->endOfMonth()->utc();
        $label = $request->month;
    } else {
        $start = null;
        $end = null;
        $label = 'all-time';
    }

    return $withLabel ? [$start, $end, $label] : [$start, $end];
}

/** Applies a resolved [start, end] range (either may be null = no filter). */
private function applyClickDateRange($query, ?Carbon $start, ?Carbon $end): void
{
    if ($start && $end) {
        $query->whereBetween('created_at', [$start, $end]);
    }
}
    private function applyEnquiryMonthFilter(
        $query,
        ?string $month
    ): void {
        if (empty($month)) {
            return;
        }

        $startDate = Carbon::createFromFormat(
            'Y-m',
            $month,
            'Asia/Kolkata'
        )->startOfMonth()->utc();

        $endDate = Carbon::createFromFormat(
            'Y-m',
            $month,
            'Asia/Kolkata'
        )->endOfMonth()->utc();

        $query->whereBetween('created_at', [
            $startDate,
            $endDate,
        ]);
    }

}