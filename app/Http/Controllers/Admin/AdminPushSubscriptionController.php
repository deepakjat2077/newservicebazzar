<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPushSubscription;
use Illuminate\Http\Request;

class AdminPushSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:2048',
            'p256dh'   => 'required|string|max:512',
            'auth'     => 'required|string|max:255',
        ]);

        $admin = auth()->guard('admin')->user();

        AdminPushSubscription::updateOrCreate(
            ['admin_id' => $admin->id, 'auth' => $validated['auth']],
            ['endpoint' => $validated['endpoint'], 'p256dh' => $validated['p256dh']]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate(['auth' => 'required|string|max:255']);

        AdminPushSubscription::where('admin_id', auth()->guard('admin')->id())
            ->where('auth', $validated['auth'])
            ->delete();

        return response()->json(['ok' => true]);
    }
}
