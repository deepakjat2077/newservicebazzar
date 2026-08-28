<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * Admin review of mobile-app KYC submissions.
 *
 * Users upload an ID document from the app (see Api\AccountController::submitKyc);
 * it lands here as `pending` (status 0). An admin approves (1) or rejects (2) —
 * the same approve/reject shape the deposit review already uses — and the user
 * is notified in-app (and by push, when Firebase is configured) of the outcome.
 */
class KycController extends Controller
{
    /** Pending by default; ?status=approved|rejected|all to switch. */
    public function index(Request $request)
    {
        $map = ['pending' => 0, 'approved' => 1, 'rejected' => 2];
        $filter = $request->query('status', 'pending');

        $query = KycSubmission::with('user')->latest();

        if (isset($map[$filter])) {
            $query->where('status', $map[$filter]);
        }

        $submissions = $query->paginate(20)->withQueryString();
        $pendingCount = KycSubmission::where('status', 0)->count();

        return view('admin.kyc.index', compact('submissions', 'filter', 'pendingCount'));
    }

    public function approve(Request $request, $id, NotificationService $notifier)
    {
        return $this->decide($request, $id, 1, $notifier);
    }

    public function reject(Request $request, $id, NotificationService $notifier)
    {
        $request->validate(['admin_note' => 'required|string|max:500']);

        return $this->decide($request, $id, 2, $notifier);
    }

    protected function decide(Request $request, $id, int $status, NotificationService $notifier)
    {
        $kyc = KycSubmission::where('id', $id)->where('status', 0)->first();

        if (!$kyc) {
            return back()->withAlert(['danger', 'This KYC has already been reviewed.']);
        }

        $kyc->status = $status;
        $kyc->admin_note = $request->input('admin_note');
        $kyc->reviewed_at = now();
        $kyc->save();

        // Let the user know, in-app and (if configured) by push.
        if ($kyc->user) {
            $notifier->send(
                $kyc->user,
                $status === 1 ? 'KYC approved' : 'KYC rejected',
                $status === 1
                    ? 'Your KYC verification has been approved.'
                    : 'Your KYC was rejected: ' . $kyc->admin_note,
                ['type' => 'kyc']
            );
        }

        return back()->withAlert(['success', 'KYC ' . ($status === 1 ? 'approved' : 'rejected') . ' successfully.']);
    }
}
