<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Listing;
use App\Models\Location;
use App\Models\WhatsappMessage;
use App\Services\WhatsappSender;
use Illuminate\Http\Request;

/**
 * The WhatsApp panel.
 *
 * Three ways in: pick listings directly, work through an enquiry (which
 * either targets one listing or every listing in that category + location),
 * or read what people sent back. Every send goes through WhatsappSender so it
 * lands in whatsapp_messages and shows up in the stats.
 */
class WhatsappController extends Controller
{
    protected WhatsappSender $sender;

    public function __construct(WhatsappSender $sender)
    {
        $this->sender = $sender;
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $stats = $this->stats();

        $recent = WhatsappMessage::outbound()
            ->with('listing')
            ->latest()
            ->limit(15)
            ->get();

        $replies = WhatsappMessage::inbound()
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.whatsapp.index', [
            'stats'      => $stats,
            'recent'     => $recent,
            'replies'    => $replies,
            'configured' => $this->sender->isConfigured(),
        ]);
    }

    /**
     * Counters for the cards at the top of every panel page.
     */
    protected function stats(): array
    {
        $outbound = WhatsappMessage::outbound();

        return [
            'total'     => (clone $outbound)->count(),
            'delivered' => (clone $outbound)->whereIn('status', WhatsappMessage::DELIVERED)->count(),
            'pending'   => (clone $outbound)->whereIn('status', WhatsappMessage::IN_FLIGHT)->count(),
            'failed'    => (clone $outbound)->where('status', 'failed')->count(),
            'replies'   => WhatsappMessage::inbound()->count(),
            'unread'    => WhatsappMessage::inbound()->unread()->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Listings tab
    |--------------------------------------------------------------------------
    */

    public function listings(Request $request)
    {
        $query = Listing::with(['user', 'category', 'location'])->latest('id');

        // Same `q` search as the All Listings screen — listing name, or the
        // owner's username/email — plus the phone, which matters here.
        if ($request->filled('q')) {
            $term = trim($request->q);

            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where('username', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->status === 'approved') {
            $query->where('status', 1);
        } elseif ($request->status === 'pending') {
            $query->where('status', 0);
        }

        // Lets you pull up only the listings still waiting on a message.
        $messaged = WhatsappMessage::outbound()
            ->whereNotNull('listing_id')
            ->select('listing_id');

        if ($request->sent === 'sent') {
            $query->whereIn('id', $messaged);
        } elseif ($request->sent === 'not_sent') {
            $query->whereNotIn('id', $messaged);
        }

        $listings = $query->paginate(25)->appends($request->query());

        return view('admin.whatsapp.listings', [
            'listings'   => $this->decorate($listings),
            'paginator'  => $listings,
            'categories' => Category::where('status', 1)->orderBy('name')->get(),
            'locations'  => Location::where('status', 1)->orderBy('name')->get(),
            'templates'  => $this->sender->templateOptions(),
            'defaultTemplate' => $this->sender->defaultTemplateId(),
            'stats'      => $this->stats(),
            'configured' => $this->sender->isConfigured(),
        ]);
    }

    /**
     * Attaches the resolved phone number and last-send info to each listing so
     * the table can render a row without repeating the lookup in Blade.
     */
    protected function decorate($listings)
    {
        $ids = collect($listings->items())->pluck('id')->all();

        $sends = WhatsappMessage::outbound()
            ->whereIn('listing_id', $ids)
            ->latest('id')
            ->get();

        $lastSends = $sends->unique('listing_id')->keyBy('listing_id');
        $counts = $sends->groupBy('listing_id')->map->count();

        return collect($listings->items())->map(function ($listing) use ($lastSends, $counts) {
            $listing->wa_phone = WhatsappSender::listingPhone($listing);
            $listing->wa_last  = $lastSends->get($listing->id);
            $listing->wa_count = $counts->get($listing->id, 0);

            return $listing;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Enquiries tab
    |--------------------------------------------------------------------------
    */

    public function enquiries(Request $request)
    {
        $query = Enquiry::with(['listing.category', 'location', 'category'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->type === 'listing') {
            $query->whereNotNull('listing_id');
        } elseif ($request->type === 'category') {
            $query->whereNull('listing_id')->whereNotNull('category_id');
        }

        $enquiries = $query->paginate(20)->appends($request->query());

        // How many messages have already gone out per enquiry, so the list can
        // show "3 sent" instead of making the admin open each one.
        $sentCounts = WhatsappMessage::outbound()
            ->whereIn('enquiry_id', collect($enquiries->items())->pluck('id'))
            ->selectRaw('enquiry_id, count(*) as total')
            ->groupBy('enquiry_id')
            ->pluck('total', 'enquiry_id');

        // The last send per enquiry: when, and which admin pressed Send.
        $lastSends = WhatsappMessage::outbound()
            ->whereIn('enquiry_id', collect($enquiries->items())->pluck('id'))
            ->orderBy('id')
            ->get(['enquiry_id', 'admin_id', 'created_at'])
            ->keyBy('enquiry_id');
        $senderNames = \App\Models\Admin::whereIn('id', $lastSends->pluck('admin_id')->filter()->unique())
            ->get(['id', 'first_name', 'last_name', 'username'])
            ->mapWithKeys(fn ($a) => [$a->id => trim($a->first_name . ' ' . $a->last_name) ?: $a->username]);

        return view('admin.whatsapp.enquiries', [
            'enquiries'  => $enquiries,
            'sentCounts' => $sentCounts,
            'lastSends'  => $lastSends,
            'senderNames' => $senderNames,
            'stats'      => $this->stats(),
            'configured' => $this->sender->isConfigured(),
        ]);
    }

    /**
     * Enquiries sent per business number: how many each WhatsApp number has been
     * sent, with category / location / listing filters. ?phone= opens one number's
     * enquiries. Category and location come from the listing the message went to.
     */
    public function sentReport(Request $request)
    {
        $base = function () use ($request) {
            $q = \Illuminate\Support\Facades\DB::table('whatsapp_messages as wm')
                ->leftJoin('listings as l', 'l.id', '=', 'wm.listing_id')
                ->where('wm.direction', 'outbound')
                ->whereNotNull('wm.enquiry_id');

            if ($request->filled('category_id')) {
                $q->where('l.category_id', (int) $request->category_id);
            }
            if ($request->filled('location_id')) {
                $q->where('l.location_id', (int) $request->location_id);
            }
            if ($request->filled('listing')) {
                $q->where('l.name', 'like', '%' . trim($request->listing) . '%');
            }
            if ($request->filled('search')) {
                $term = trim($request->search);
                $digits = preg_replace('/\D/', '', $term);
                $q->where(function ($w) use ($term, $digits) {
                    $w->where('wm.contact_name', 'like', '%' . $term . '%');
                    if (strlen($digits) >= 4) {
                        $w->orWhere('wm.phone', 'like', '%' . $digits . '%');
                    }
                });
            }

            return $q;
        };

        $phone = preg_replace('/\D/', '', (string) $request->phone);

        $summary = $base()
            ->selectRaw('COUNT(DISTINCT wm.phone) AS numbers, COUNT(DISTINCT wm.enquiry_id) AS enquiries')
            ->first();

        $rows = null;
        $sent = null;
        $listings = collect();

        if ($phone !== '') {
            // One number: every enquiry it was sent.
            $sent = $base()
                ->where('wm.phone', $phone)
                ->leftJoin('enquiries as e', 'e.id', '=', 'wm.enquiry_id')
                ->selectRaw('wm.id, wm.enquiry_id, wm.status, wm.created_at, wm.contact_name, wm.listing_id, e.name AS enquirer, e.phone AS enquirer_phone')
                ->orderByDesc('wm.id')
                ->paginate(25)
                ->withQueryString();
            $listingIds = collect($sent->items())->pluck('listing_id');
        } else {
            $rows = $base()
                ->selectRaw('wm.phone, MAX(wm.contact_name) AS contact_name, MAX(wm.listing_id) AS listing_id, COUNT(DISTINCT wm.enquiry_id) AS total, MAX(wm.created_at) AS last_sent')
                ->groupBy('wm.phone')
                ->orderByDesc('total')
                ->orderByDesc('last_sent')
                ->paginate(25)
                ->withQueryString();
            $listingIds = collect($rows->items())->pluck('listing_id');
        }

        $listings = Listing::with(['category:id,name', 'location:id,name'])
            ->whereIn('id', $listingIds->filter()->unique())
            ->get(['id', 'name', 'category_id', 'location_id'])
            ->keyBy('id');

        return view('admin.whatsapp.sent_report', [
            'rows'       => $rows,
            'sent'       => $sent,
            'phone'      => $phone,
            'summary'    => $summary,
            'listings'   => $listings,
            'categories' => Category::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'locations'  => Location::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'stats'      => $this->stats(),
            'configured' => $this->sender->isConfigured(),
        ]);
    }

    /**
     * One enquiry and everything it can be forwarded to.
     *
     * A listing enquiry has exactly one target. A category enquiry ("someone
     * asked for Physiotherapists in Jaipur") fans out to every approved
     * listing in that category and location.
     */
    public function enquiry($id)
    {
        $enquiry = Enquiry::with(['listing.category', 'location', 'category'])->findOrFail($id);

        $targets = $this->targetsFor($enquiry);

        $sent = WhatsappMessage::outbound()
            ->where('enquiry_id', $enquiry->id)
            ->with('listing')
            ->latest()
            ->get();

        // Show the admin exactly what will land in the template's variables,
        // using the first target as the sample.
        $preview = $targets->isNotEmpty()
            ? WhatsappSender::enquiryParams($enquiry, $targets->first())
            : [];

        return view('admin.whatsapp.enquiry', [
            'enquiry'         => $enquiry,
            'targets'         => $targets,
            'sent'            => $sent,
            'preview'         => $preview,
            'templates'       => $this->sender->templateOptions(),
            // An enquiry defaults to the enquiry template, not the marketing one.
            'defaultTemplate' => $this->sender->enquiryTemplateId()
                ?? $this->sender->defaultTemplateId(),
            'stats'           => $this->stats(),
            'configured'      => $this->sender->isConfigured(),
        ]);
    }

    /**
     * Listings an enquiry should be forwarded to.
     */
    protected function targetsFor(Enquiry $enquiry)
    {
        if ($enquiry->listing_id && $enquiry->listing) {
            $listings = collect([$enquiry->listing]);
        } elseif ($enquiry->category_id) {
            $query = Listing::with(['category', 'location'])
                ->where('category_id', $enquiry->category_id)
                ->where('status', 1);

            // Keep it to the city the enquiry came from when we know it —
            // otherwise a category enquiry would blast the whole country.
            if ($enquiry->location_id) {
                $query->where('location_id', $enquiry->location_id);
            }

            $listings = $query->orderByDesc('is_featured')->orderBy('name')->limit(200)->get();
        } else {
            $listings = collect();
        }

        $alreadySent = WhatsappMessage::outbound()
            ->where('enquiry_id', $enquiry->id)
            ->pluck('status', 'listing_id');

        return $listings->map(function ($listing) use ($alreadySent) {
            $listing->wa_phone = WhatsappSender::listingPhone($listing);
            $listing->wa_sent  = $alreadySent->get($listing->id);

            return $listing;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Sending
    |--------------------------------------------------------------------------
    */

    /**
     * Send one template to every selected listing. Returns a per-listing
     * breakdown so the page can show exactly which ones failed and why.
     */
    public function send(Request $request)
    {
        $request->validate([
            'listing_ids'   => 'required|array|min:1|max:200',
            'listing_ids.*' => 'integer',
            'template_id'   => 'required|string|max:191',
            'enquiry_id'    => 'nullable|integer|exists:enquiries,id',
            'params'        => 'nullable|string|max:2000',
        ]);

        if (!$this->sender->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Gupshup is not configured. Open Settings > WhatsApp Setting first.',
            ], 422);
        }

        // Custom params, one per line, override whatever the template would
        // normally get. Blank means "work it out from the enquiry/listing".
        $override = null;

        if ($request->filled('params')) {
            $override = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $request->params)),
                function ($line) {
                    return $line !== '';
                }
            ));
        }

        $enquiry = $request->enquiry_id
            ? Enquiry::with(['location', 'category'])->find($request->enquiry_id)
            : null;

        $listings = Listing::with(['category', 'location'])
            ->whereIn('id', $request->listing_ids)
            ->get();

        if ($listings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No matching listings found.',
            ], 422);
        }

        $meta = [
            'enquiry_id' => $request->enquiry_id,
            'admin_id'   => optional(auth()->guard('admin')->user())->id,
        ];

        $results = [];
        $sent = 0;
        $failed = 0;

        foreach ($listings as $listing) {
            // Forwarding an enquiry fills the enquiry template's seven
            // variables from this listing plus the enquiry; a plain listing
            // send falls through to WhatsappSender's [name, url] default.
            $params = $override;

            if ($params === null && $enquiry) {
                $params = WhatsappSender::enquiryParams($enquiry, $listing);
            }

            $message = $this->sender->sendToListing(
                $listing,
                trim($request->template_id),
                $params,
                $meta
            );

            if ($message->isFailed()) {
                $failed++;
            } else {
                $sent++;
            }

            $results[] = [
                'listing_id' => $listing->id,
                'name'       => $listing->name,
                'phone'      => $message->phone,
                'status'     => $message->status,
                'error'      => $message->error,
            ];
        }

        return response()->json([
            'success' => $failed === 0,
            'sent'    => $sent,
            'failed'  => $failed,
            'message' => $failed === 0
                ? $sent . ' message(s) submitted successfully.'
                : $sent . ' sent, ' . $failed . ' failed.',
            'results' => $results,
            'stats'   => $this->stats(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Replies
    |--------------------------------------------------------------------------
    */

    public function replies(Request $request)
    {
        $query = WhatsappMessage::inbound()->latest();

        if ($request->filter === 'unread') {
            $query->unread();
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', '%' . $search . '%')
                  ->orWhere('contact_name', 'like', '%' . $search . '%')
                  ->orWhere('body', 'like', '%' . $search . '%');
            });
        }

        $replies = $query->paginate(25)->appends($request->query());

        return view('admin.whatsapp.replies', [
            'replies'    => $replies,
            'stats'      => $this->stats(),
            'configured' => $this->sender->isConfigured(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Chats
    |--------------------------------------------------------------------------
    */

    /**
     * One row per phone number we have exchanged messages with, newest first.
     */
    /**
     * The conversation list that fills the left pane. Both the bare chats
     * page and an open thread render it, so it lives in one place.
     */
    protected function conversationList(Request $request): array
    {
        $latestIds = WhatsappMessage::selectRaw('MAX(id) as id')
            ->groupBy('phone')
            ->pluck('id');

        $query = WhatsappMessage::with('listing')
            ->whereIn('id', $latestIds)
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', '%' . $search . '%')
                  ->orWhere('contact_name', 'like', '%' . $search . '%');
            });
        }

        return [
            'conversations' => $query->paginate(25)->appends($request->query()),
            // Unread inbound count per number, for the badge on each row.
            'unread'        => WhatsappMessage::inbound()
                ->unread()
                ->selectRaw('phone, count(*) as total')
                ->groupBy('phone')
                ->pluck('total', 'phone'),
        ];
    }

    public function chats(Request $request)
    {
        $list = $this->conversationList($request);

        /*
         * Polled by the open page so a new conversation — or a new message on
         * an existing one — shows up in the left pane on its own. Without this
         * the list was only ever as fresh as the last full page load, which is
         * why an incoming message needed a refresh to appear.
         */
        if ($request->ajax()) {
            $stats = $this->stats();

            return response()->json([
                'html'   => view('admin.whatsapp.partials.chat-list', array_merge($list, [
                    'activePhone' => $request->query('active'),
                ]))->render(),
                'unread' => $stats['unread'],
            ]);
        }

        return view('admin.whatsapp.chats', array_merge($list, [
            'activePhone' => null,
            'thread'      => null,
            'stats'       => $this->stats(),
            'configured'  => $this->sender->isConfigured(),
        ]));
    }

    /**
     * The full thread with one number, plus the reply box.
     */
    public function chat(Request $request, $phone)
    {
        $phone = preg_replace('/\D+/', '', $phone);

        abort_if($phone === '', 404);

        $messages = WhatsappMessage::where('phone', $phone)
            ->with('listing')
            ->orderBy('id')
            ->get();

        abort_if($messages->isEmpty(), 404);

        // Opening the thread is reading it.
        WhatsappMessage::inbound()->where('phone', $phone)->unread()
            ->update(['read_at' => now()]);

        $name = $messages->pluck('contact_name')->filter()->last();

        $thread = [
            'phone'       => $phone,
            'name'        => $name,
            'messages'    => $messages,
            'listing'     => optional($messages->last())->listing,
            'sessionOpen' => WhatsappSender::sessionOpenFor($phone),
            'sessionEnds' => WhatsappSender::sessionExpiresAt($phone),
        ];

        // Clicking a conversation swaps only the right-hand pane, so hand
        // back just that markup. A direct visit (or no JS) still gets the
        // whole split screen with this thread already open.
        if ($request->ajax()) {
            return view('admin.whatsapp.partials.thread', $thread);
        }

        return view('admin.whatsapp.chats', array_merge(
            $this->conversationList($request),
            [
                'activePhone' => $phone,
                'thread'      => $thread,
                'stats'       => $this->stats(),
                'configured'  => $this->sender->isConfigured(),
            ]
        ));
    }

    /**
     * Send a typed reply into an open 24-hour window.
     */
    public function replyToChat(Request $request, $phone)
    {
        $request->validate([
            'body' => 'required|string|max:4000',
        ]);

        $phone = preg_replace('/\D+/', '', $phone);

        $message = $this->sender->sendText($phone, $request->body, [
            'admin_id'     => optional(auth()->guard('admin')->user())->id,
            'contact_name' => WhatsappMessage::where('phone', $phone)
                ->whereNotNull('contact_name')
                ->latest('id')
                ->value('contact_name'),
            'listing_id'   => WhatsappMessage::where('phone', $phone)
                ->whereNotNull('listing_id')
                ->latest('id')
                ->value('listing_id'),
        ]);

        // The chat box posts over AJAX so the bubble can appear without a
        // page reload; the plain form post is kept as a fallback.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => !$message->isFailed(),
                'message' => $message->isFailed() ? $message->error : 'Reply sent',
                'data'    => [
                    'id'          => $message->id,
                    'direction'   => $message->direction,
                    'body'        => $message->displayBody(),
                    'unsupported' => $message->isUnsupported(),
                    'revoked'     => $message->isRevoke(),
                    'delete_url'  => route('admin.whatsapp.message.delete', $message->id),
                    'status'      => $message->status,
                    'error'       => $message->error,
                    'created_at'  => $message->created_at->toIso8601String(),
                    'tick'        => $message->tick(),
                ],
            ], $message->isFailed() ? 422 : 200);
        }

        $alert = $message->isFailed()
            ? ['danger', $message->error]
            : ['success', 'Reply sent'];

        return back()->withAlert($alert);
    }

    /** Polled by the open thread so an incoming reply appears on its own. */
    public function chatMessages($phone)
    {
        $phone = preg_replace('/\D+/', '', $phone);

        $messages = WhatsappMessage::where('phone', $phone)
            ->orderBy('id')
            ->get()
            ->map(function ($message) {
                return [
                    'id'          => $message->id,
                    'direction'   => $message->direction,
                    'body'        => $message->displayBody(),
                    'unsupported' => $message->isUnsupported(),
                    'revoked'     => $message->isRevoke(),
                    'delete_url'  => route('admin.whatsapp.message.delete', $message->id),
                    'status'      => $message->status,
                    'error'       => $message->error,
                    'created_at'  => $message->created_at->toIso8601String(),
                    'media'       => $message->mediaLink(),
                    'is_image'    => $message->isImage(),
                    // Lets the poll repaint the ticks on bubbles that are
                    // already on screen — a message goes sent → delivered →
                    // read long after it was first drawn.
                    'tick'       => $message->tick(),
                ];
            });

        WhatsappMessage::inbound()->where('phone', $phone)->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'messages'     => $messages,
            'session_open' => WhatsappSender::sessionOpenFor($phone),
        ]);
    }

    public function markReplyRead($id)
    {
        $reply = WhatsappMessage::inbound()->findOrFail($id);

        $reply->update(['read_at' => now()]);

        $alert = ['success', 'Reply marked as read'];

        return back()->withAlert($alert);
    }

    public function markAllRepliesRead()
    {
        WhatsappMessage::inbound()->unread()->update(['read_at' => now()]);

        $alert = ['success', 'All replies marked as read'];

        return back()->withAlert($alert);
    }

    /**
     * Remove one message from the panel.
     *
     * This clears our own record only — WhatsApp keeps its copy and the
     * customer still sees the message on their handset. There is no API to
     * unsend it.
     */
    public function deleteMessage(Request $request, $id)
    {
        $message = WhatsappMessage::findOrFail($id);

        $this->forgetMedia($message);

        $message->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->withAlert(['success', 'Message deleted']);
    }

    /** Same, for every message exchanged with one number. */
    public function deleteChat(Request $request, $phone)
    {
        $phone = preg_replace('/\D+/', '', $phone);

        abort_if($phone === '', 404);

        $messages = WhatsappMessage::where('phone', $phone)->get();

        abort_if($messages->isEmpty(), 404);

        foreach ($messages as $message) {
            $this->forgetMedia($message);
        }

        WhatsappMessage::where('phone', $phone)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.whatsapp.chats')
            ->withAlert(['success', 'Conversation deleted']);
    }

    /**
     * Drop the copy of an attachment we downloaded, so deleting a message
     * doesn't leave orphaned files in public/assets. Only paths we wrote
     * ourselves are touched — media_url points at the provider, not at us.
     */
    protected function forgetMedia(WhatsappMessage $message): void
    {
        if (!$message->media_path) {
            return;
        }

        $path = public_path($message->media_path);

        if (str_starts_with($path, public_path('assets/images/whatsapp/')) && is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Polled by the panel so a new reply pops a toast without a page reload.
     */
    public function unreadCount()
    {
        $unread = WhatsappMessage::inbound()->unread();

        return response()->json([
            'count'  => (clone $unread)->count(),
            'latest' => (clone $unread)->latest()->limit(5)->get([
                'id', 'phone', 'contact_name', 'body', 'created_at',
            ]),
        ]);
    }
}
