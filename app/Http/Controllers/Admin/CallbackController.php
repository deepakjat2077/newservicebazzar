<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Illuminate\Http\Request;

/**
 * Admin view of "Get a Callback" requests from the mobile app's Help screen.
 * The team sees the pending requests, calls the number, and marks each handled.
 */
class CallbackController extends Controller
{
    public function index(Request $request)
    {
        $map = ['pending' => 0, 'handled' => 1];
        $filter = $request->query('status', 'pending');

        $query = CallbackRequest::with('user')->latest();

        if (isset($map[$filter])) {
            $query->where('status', $map[$filter]);
        }

        $requests = $query->paginate(20)->withQueryString();
        $pendingCount = CallbackRequest::where('status', 0)->count();

        return view('admin.callback.index', compact('requests', 'filter', 'pendingCount'));
    }

    public function markHandled(Request $request, $id)
    {
        $callback = CallbackRequest::where('id', $id)->where('status', 0)->first();

        if (!$callback) {
            return back()->withAlert(['danger', 'This request has already been handled.']);
        }

        $callback->status = 1;
        $callback->admin_note = $request->input('admin_note');
        $callback->handled_at = now();
        $callback->save();

        return back()->withAlert(['success', 'Marked as handled.']);
    }
}
