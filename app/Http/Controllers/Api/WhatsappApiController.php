<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Listing;
use App\Models\Location;
use App\Models\WhatsappMessage;
use App\Services\WhatsappSender;
use Illuminate\Http\Request;

/**
 * JSON surface for the external WhatsApp panel.
 *
 * Mirrors Admin\WhatsappController screen for screen, with the Blade views
 * replaced by plain arrays. Everything that actually *does* something — the
 * Gupshup calls, the 24-hour session window, template parameters — still goes
 * through WhatsappSender, so the API and the Blade panel can never disagree
 * about what a send does.
 *
 * NOTE: the query/filter logic here deliberately duplicates the Blade
 * controller's rather than refactoring it, so the live panel keeps working
 * untouched while the React panel is built. Once React is in production the
 * Blade panel (and its views) should be retired and this becomes the only
 * copy — otherwise the two will drift.
 *
 * Auth: every route is behind `admin.api` (bearer token → admin guard) plus
 * `admin.api.can:whatsapp.*`, so auth()->guard('admin')->user() is always set
 * and WhatsappSender stamps the right admin_id.
 */
class WhatsappApiController extends Controller
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
        return response()->json([
            'success'    => true,
            'stats'      => $this->stats(),
            'configured' => $this->sender->isConfigured(),
            'recent'     => WhatsappMessage::outbound()->with('listing')->latest()->limit(15)
                ->get()->map(fn ($m) => $this->messageRow($m)),
            'replies'    => WhatsappMessage::inbound()->latest()->limit(10)
                ->get()->map(fn ($m) => $this->messageRow($m)),
        ]);
    }

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

    /**
     * Dropdown data for the panel's filter bars, fetched once on boot instead
     * of being re-sent with every listing page.
     */
    public function filters()
    {
        return response()->json([
            'success'    => true,
            'categories' => Category::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'locations'  => Location::where('status', 1)->orderBy('name')->get(['id', 'name']),
            // templateOptions() is keyed [id => label], which json_encode turns
            // into an object — and an object has no .map(), so the panel's
            // template dropdown blew up on it. Everything else here is a list
            // of {id, name}; make this one match instead of making each client
            // special-case it.
            'templates'  => collect($this->sender->templateOptions())
                ->map(fn ($label, $id) => ['id' => (string) $id, 'name' => $label])
                ->values(),
            'defaults'   => [
                'listing' => $this->sender->defaultTemplateId(),
                'enquiry' => $this->sender->enquiryTemplateId() ?? $this->sender->defaultTemplateId(),
            ],
            'configured' => $this->sender->isConfigured(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Listings
    |--------------------------------------------------------------------------
    */

    public function listings(Request $request)
    {
        $query = Listing::with(['user', 'category', 'location'])->latest('id');

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

        $messaged = WhatsappMessage::outbound()->whereNotNull('listing_id')->select('listing_id');

        if ($request->sent === 'sent') {
            $query->whereIn('id', $messaged);
        } elseif ($request->sent === 'not_sent') {
            $query->whereNotIn('id', $messaged);
        }

        $listings = $query->paginate($this->perPage($request, 25));

        return response()->json([
            'success' => true,
            'data'    => $this->decorate($listings),
            'meta'    => $this->paginationMeta($listings),
            'stats'   => $this->stats(),
        ]);
    }

    /** Resolved phone + last-send info per listing, so the table needs no extra calls. */
    protected function decorate($listings)
    {
        $ids = collect($listings->items())->pluck('id')->all();

        $sends = WhatsappMessage::outbound()->whereIn('listing_id', $ids)->latest('id')->get();

        $lastSends = $sends->unique('listing_id')->keyBy('listing_id');
        $counts = $sends->groupBy('listing_id')->map->count();

        return collect($listings->items())->map(function ($listing) use ($lastSends, $counts) {
            $last = $lastSends->get($listing->id);

            return [
                'id'       => $listing->id,
                'name'     => $listing->name,
                'mobile'   => $listing->mobile,
                'email'    => $listing->email,
                'status'   => (int) $listing->status,
                'category' => $listing->category?->name,
                'location' => $listing->location?->name,
                'owner'    => $listing->user?->username,
                'wa_phone' => WhatsappSender::listingPhone($listing),
                'wa_count' => $counts->get($listing->id, 0),
                'wa_last'  => $last ? [
                    'status'     => $last->status,
                    'error'      => $last->error,
                    'created_at' => $last->created_at?->toIso8601String(),
                    'tick'       => $last->tick(),
                ] : null,
            ];
        })->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Enquiries
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

        $enquiries = $query->paginate($this->perPage($request, 20));

        $sentCounts = WhatsappMessage::outbound()
            ->whereIn('enquiry_id', collect($enquiries->items())->pluck('id'))
            ->selectRaw('enquiry_id, count(*) as total')
            ->groupBy('enquiry_id')
            ->pluck('total', 'enquiry_id');

        return response()->json([
            'success' => true,
            'data'    => collect($enquiries->items())->map(fn ($e) => $this->enquiryRow($e, $sentCounts)),
            'meta'    => $this->paginationMeta($enquiries),
            'stats'   => $this->stats(),
        ]);
    }

    /** One enquiry plus every listing it can be forwarded to. */
    public function enquiry($id)
    {
        $enquiry = Enquiry::with(['listing.category', 'location', 'category'])->findOrFail($id);

        $targets = $this->targetsFor($enquiry);

        $sent = WhatsappMessage::outbound()
            ->where('enquiry_id', $enquiry->id)
            ->with('listing')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'enquiry' => $this->enquiryRow($enquiry),
            'targets' => $targets,
            'sent'    => $sent->map(fn ($m) => $this->messageRow($m)),
            // What the template's variables will actually be filled with,
            // sampled from the first target — same preview the Blade panel shows.
            'preview' => $targets->isNotEmpty()
                ? WhatsappSender::enquiryParams($enquiry, Listing::find($targets->first()['id']))
                : [],
            'default_template' => $this->sender->enquiryTemplateId() ?? $this->sender->defaultTemplateId(),
        ]);
    }

    protected function targetsFor(Enquiry $enquiry)
    {
        if ($enquiry->listing_id && $enquiry->listing) {
            $listings = collect([$enquiry->listing]);
        } elseif ($enquiry->category_id) {
            $query = Listing::with(['category', 'location'])
                ->where('category_id', $enquiry->category_id)
                ->where('status', 1);

            // Stay inside the city the enquiry came from — otherwise a
            // category enquiry would blast every listing in the country.
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

        return $listings->map(fn ($listing) => [
            'id'       => $listing->id,
            'name'     => $listing->name,
            'category' => $listing->category?->name,
            'location' => $listing->location?->name,
            'wa_phone' => WhatsappSender::listingPhone($listing),
            'wa_sent'  => $alreadySent->get($listing->id),
        ])->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Sending
    |--------------------------------------------------------------------------
    */

    /** One template to every selected listing, with a per-listing result breakdown. */
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

        // One override per line; blank means "work it out from the enquiry".
        $override = null;

        if ($request->filled('params')) {
            $override = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $request->params)),
                fn ($line) => $line !== ''
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
            // Set by AuthenticateAdminApi from the bearer token.
            'admin_id'   => optional(auth()->guard('admin')->user())->id,
        ];

        $results = [];
        $sent = 0;
        $failed = 0;

        foreach ($listings as $listing) {
            $params = $override;

            if ($params === null && $enquiry) {
                $params = WhatsappSender::enquiryParams($enquiry, $listing);
            }

            $message = $this->sender->sendToListing($listing, trim($request->template_id), $params, $meta);

            $message->isFailed() ? $failed++ : $sent++;

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

        $replies = $query->paginate($this->perPage($request, 25));

        return response()->json([
            'success' => true,
            'data'    => collect($replies->items())->map(fn ($m) => $this->messageRow($m)),
            'meta'    => $this->paginationMeta($replies),
            'stats'   => $this->stats(),
        ]);
    }

    public function markReplyRead($id)
    {
        $reply = WhatsappMessage::inbound()->findOrFail($id);

        $reply->update(['read_at' => now()]);

        return response()->json(['success' => true, 'stats' => $this->stats()]);
    }

    public function markAllRepliesRead()
    {
        WhatsappMessage::inbound()->unread()->update(['read_at' => now()]);

        return response()->json(['success' => true, 'stats' => $this->stats()]);
    }

    /** Polled so a new reply can raise a toast without a reload. */
    public function unreadCount()
    {
        $unread = WhatsappMessage::inbound()->unread();

        return response()->json([
            'success' => true,
            'count'   => (clone $unread)->count(),
            'latest'  => (clone $unread)->latest()->limit(5)->get()
                ->map(fn ($m) => $this->messageRow($m)),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Chats
    |--------------------------------------------------------------------------
    */

    /** One row per phone number, newest first — the panel's left pane. */
    public function conversations(Request $request)
    {
        $latestIds = WhatsappMessage::selectRaw('MAX(id) as id')->groupBy('phone')->pluck('id');

        $query = WhatsappMessage::with('listing')->whereIn('id', $latestIds)->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', '%' . $search . '%')
                  ->orWhere('contact_name', 'like', '%' . $search . '%');
            });
        }

        $conversations = $query->paginate($this->perPage($request, 25));

        $unread = WhatsappMessage::inbound()->unread()
            ->selectRaw('phone, count(*) as total')
            ->groupBy('phone')
            ->pluck('total', 'phone');

        return response()->json([
            'success' => true,
            'data'    => collect($conversations->items())->map(fn ($m) => [
                'phone'        => $m->phone,
                'name'         => $m->contact_name,
                'last_message' => $m->displayBody(),
                'direction'    => $m->direction,
                'avatar'       => $m->avatarImage(),
                'listing'      => $m->listing?->name,
                'unread'       => (int) $unread->get($m->phone, 0),
                'updated_at'   => $m->created_at?->toIso8601String(),
            ]),
            'meta'  => $this->paginationMeta($conversations),
            'stats' => $this->stats(),
        ]);
    }

    /**
     * The full thread with one number.
     *
     * Opening it marks the inbound side read, exactly as the Blade panel does.
     */
    public function thread(Request $request, $phone)
    {
        $phone = $this->normalisePhone($phone);

        if ($phone === '') {
            return response()->json(['success' => false, 'message' => 'Invalid phone number.'], 422);
        }

        $messages = WhatsappMessage::where('phone', $phone)->with('listing')->orderBy('id')->get();

        if ($messages->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Conversation not found.'], 404);
        }

        WhatsappMessage::inbound()->where('phone', $phone)->unread()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'phone'   => $phone,
            'name'    => $messages->pluck('contact_name')->filter()->last(),
            'listing' => optional($messages->last())->listing?->only(['id', 'name']),
            // Outside the 24-hour window WhatsApp only allows templates, so
            // the panel has to disable the free-text box.
            'session_open' => WhatsappSender::sessionOpenFor($phone),
            'session_ends' => optional(WhatsappSender::sessionExpiresAt($phone))->toIso8601String(),
            'messages'     => $messages->map(fn ($m) => $this->messageRow($m)),
        ]);
    }

    /** Free-text reply, only valid inside the open 24-hour window. */
    public function reply(Request $request, $phone)
    {
        $request->validate(['body' => 'required|string|max:4000']);

        $phone = $this->normalisePhone($phone);

        if ($phone === '') {
            return response()->json(['success' => false, 'message' => 'Invalid phone number.'], 422);
        }

        $message = $this->sender->sendText($phone, $request->body, [
            'admin_id'     => optional(auth()->guard('admin')->user())->id,
            'contact_name' => WhatsappMessage::where('phone', $phone)
                ->whereNotNull('contact_name')->latest('id')->value('contact_name'),
            'listing_id'   => WhatsappMessage::where('phone', $phone)
                ->whereNotNull('listing_id')->latest('id')->value('listing_id'),
        ]);

        return response()->json([
            'success' => !$message->isFailed(),
            'message' => $message->isFailed() ? $message->error : 'Reply sent',
            'data'    => $this->messageRow($message),
        ], $message->isFailed() ? 422 : 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Deletion
    |--------------------------------------------------------------------------
    */

    public function deleteMessage($id)
    {
        $message = WhatsappMessage::findOrFail($id);

        $this->forgetMedia($message);
        $message->delete();

        return response()->json(['success' => true, 'stats' => $this->stats()]);
    }

    public function deleteConversation($phone)
    {
        $phone = $this->normalisePhone($phone);

        $messages = WhatsappMessage::where('phone', $phone)->get();

        if ($phone === '' || $messages->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Conversation not found.'], 404);
        }

        foreach ($messages as $message) {
            $this->forgetMedia($message);
        }

        WhatsappMessage::where('phone', $phone)->delete();

        return response()->json(['success' => true, 'stats' => $this->stats()]);
    }

    /**
     * Drop our downloaded copy of an attachment so a delete leaves no orphan
     * in public/assets. Only paths we wrote ourselves — media_url points at
     * the provider, not at us.
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

    /*
    |--------------------------------------------------------------------------
    | Shapers
    |--------------------------------------------------------------------------
    */

    /**
     * One message, in the shape every screen renders it.
     *
     * displayBody()/isRevoke()/tick() live on the model, so the panel gets the
     * same interpretation of a bubble that the Blade views get — the React
     * side never has to re-implement "what does [unsupported] mean".
     */
    protected function messageRow(WhatsappMessage $message): array
    {
        return [
            'id'          => $message->id,
            'direction'   => $message->direction,
            'phone'       => $message->phone,
            'name'        => $message->contact_name,
            'body'        => $message->displayBody(),
            'unsupported' => $message->isUnsupported(),
            'revoked'     => $message->isRevoke(),
            'status'      => $message->status,
            'error'       => $message->error,
            'tick'        => $message->tick(),
            'media'       => $message->mediaLink(),
            'is_image'    => $message->isImage(),
            'listing'     => $message->listing?->only(['id', 'name']),
            'enquiry_id'  => $message->enquiry_id,
            'read_at'     => $message->read_at?->toIso8601String(),
            'created_at'  => $message->created_at?->toIso8601String(),
        ];
    }

    protected function enquiryRow(Enquiry $enquiry, $sentCounts = null): array
    {
        return [
            'id'         => $enquiry->id,
            'name'       => $enquiry->name,
            'phone'      => $enquiry->phone,
            'email'      => $enquiry->email,
            'is_read'    => (bool) $enquiry->is_read,
            'type'       => $enquiry->listing_id ? 'listing' : 'category',
            'listing'    => $enquiry->listing?->only(['id', 'name']),
            'category'   => $enquiry->category?->name,
            'location'   => $enquiry->location?->name,
            'sent_count' => $sentCounts ? (int) $sentCounts->get($enquiry->id, 0) : null,
            'created_at' => $enquiry->created_at?->toIso8601String(),
        ];
    }

    protected function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'from'         => $paginator->firstItem(),
            'to'           => $paginator->lastItem(),
        ];
    }

    /** Clamped so a client cannot ask for the whole table in one response. */
    protected function perPage(Request $request, int $default): int
    {
        return min(max((int) $request->input('per_page', $default), 1), 100);
    }

    protected function normalisePhone($phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone);
    }
}
