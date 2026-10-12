<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

/**
 * The bell in the site header: the signed-in user's notifications (new
 * enquiries and the rest of app_notifications), newest first.
 */
class UserNotificationController extends Controller
{
    public function feed(Request $request)
    {
        $userId = $request->user()->id;

        $items = AppNotification::where('user_id', $userId)->latest('id')->take(30)->get();

        return response()->json([
            'unread' => AppNotification::where('user_id', $userId)->where('is_read', false)->count(),
            'items'  => $items->map(fn ($n) => [
                'id'    => $n->id,
                'title' => (string) $n->title,
                'body'  => (string) $n->body,
                'link'  => $n->link,
                'read'  => (bool) $n->is_read,
                'ago'   => optional($n->created_at)->diffForHumans(),
            ])->values(),
        ]);
    }

    public function read(Request $request, $id)
    {
        AppNotification::where('user_id', $request->user()->id)->whereKey($id)->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function readAll(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }
}
