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
        ]);

        $query = ListingClick::query()->latest();

        $this->applyClickMonthFilter($query, $request->month);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $clicks = $query->paginate(15);

        /*
         * Month-wise counts.
         */
        $callQuery = ListingClick::where('type', 'call');
        $whatsappQuery = ListingClick::where('type', 'whatsapp');
        $enquiryQuery = ListingClick::where('type', 'enquiry');
        $totalQuery = ListingClick::query();

        $this->applyClickMonthFilter(
            $callQuery,
            $request->month
        );

        $this->applyClickMonthFilter(
            $whatsappQuery,
            $request->month
        );

        $this->applyClickMonthFilter(
            $enquiryQuery,
            $request->month
        );

        $this->applyClickMonthFilter(
            $totalQuery,
            $request->month
        );

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
    $validated = $request->validate([
        'month' => [
            'nullable',
            'date_format:Y-m',
        ],
    ]);

    $month = $validated['month'] ?? null;
    $fileMonth = $month ?: 'all-months';

    $fileName = 'listing-clicks-report-' .
        $fileMonth .
        '-' .
        now('Asia/Kolkata')->format('Ymd-His') .
        '.xlsx';

    return Excel::download(
        new ListingClicksExport($month),
        $fileName
    );
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

    private function applyClickMonthFilter(
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