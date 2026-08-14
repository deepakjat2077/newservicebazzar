<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestConversation;
use App\Models\GuestMessage;
use Illuminate\Http\Request;

class GuestChatAdminController extends Controller
{
    public function index()
    {
        $pageTitle = 'Guest Chats';

        $conversations = GuestConversation::with(['guestUser', 'listing'])
            ->withCount([
                'messages as unread_count' => function ($q) {
                    $q->where('sender_type', 'guest')->whereNull('read_at');
                }
            ])
            ->latest('last_message_at')
            ->paginate(20);

        return view('admin.guest_chats.index', compact('pageTitle', 'conversations'));
    }

    public function show(GuestConversation $conversation)
    {
        $pageTitle = 'Guest Chat';

        $conversation->load(['guestUser', 'listing.category']);

        $messages = $conversation->messages()
            ->orderBy('id', 'asc')
            ->get();

        GuestMessage::where('conversation_id', $conversation->id)
            ->where('sender_type', 'guest')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.guest_chats.show', compact('pageTitle', 'conversation', 'messages'));
    }

    public function send(Request $request, GuestConversation $conversation)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'owner',
            'body' => trim(strip_tags($request->message)),
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'body' => $message->body,
                'created_at' => $message->created_at?->format('d M Y, H:i'),
            ],
        ]);
    }

    public function messages(Request $request, GuestConversation $conversation)
    {
        $afterId = (int) $request->get('after_id', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get();

        GuestMessage::whereIn('id', $messages->where('sender_type', 'guest')->pluck('id'))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'ok' => true,
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->format('d M Y, H:i'),
                ];
            }),
        ]);
    }
}