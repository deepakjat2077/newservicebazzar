<?php

namespace App\Http\Controllers;

use App\Models\WebPushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:2048',
            'p256dh'   => 'required|string|max:512',
            'auth'     => 'required|string|max:255',
        ]);

        $user = $request->user();

        WebPushSubscription::updateOrCreate(
            ['user_id' => $user->id, 'auth' => $validated['auth']],
            ['endpoint' => $validated['endpoint'], 'p256dh' => $validated['p256dh']]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate(['auth' => 'required|string|max:255']);

        WebPushSubscription::where('user_id', $request->user()->id)
            ->where('auth', $validated['auth'])
            ->delete();

        return response()->json(['ok' => true]);
    }
}
