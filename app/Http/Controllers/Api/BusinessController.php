<?php

namespace App\Http\Controllers\Api;

use App\Events\AdminNotification;
use App\Http\Controllers\Admin\PincodeController;
use App\Http\Controllers\Controller;
use App\Models\AppFeedback;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\ExtraImage;
use App\Models\Listing;
use App\Models\ListingDailyStats;
use App\Models\Location;
use App\Models\Rating;
use App\Models\Subcategory;
use Carbon\Carbon;
use HTMLPurifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * "My Business" for the mobile app — the business-owner side of the account.
 *
 * JSON twin of the owner screens in App\Http\Controllers\ListingController:
 * the "Select Business Profile" list, each business's dashboard (enquiry
 * counts, response rate, quotation count), the leads/enquiries screen with all
 * its filters, analytics / response-time / competitors, and CSV/Excel lead
 * import. Every number is derived from the same tables the website reads
 * (enquiries, listing_daily_stats, ratings) — nothing is invented, matching
 * the web controller's own rule about not fabricating figures this site has no
 * way to know.
 *
 * Every route runs behind `user.api`, and each listing/enquiry is scoped to
 * the token owner (ownedListing/ownedEnquiry) so one owner can never read or
 * touch another's business.
 */
class BusinessController extends Controller
{
    /* ---------------------------------------------------------------------
     | My Business — the "Select Business Profile" list
     | -------------------------------------------------------------------*/

    public function index(Request $request)
    {
        $listings = Listing::where('user_id', $request->user()->id)
            ->with('location', 'category')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success'    => true,
            'businesses' => $listings->map(fn ($l) => $this->businessCard($l))->values(),
        ]);
    }

    /* ---------------------------------------------------------------------
     | Add New Business — the multi-step wizard
     | -------------------------------------------------------------------*/

    /**
     * Dropdown data the "Add Business" wizard needs up front: the category
     * list (step "Add Business Category") and the location list (step
     * "Address" > Location). Same sources the website's listing form uses.
     */
    public function formData()
    {
        return response()->json([
            'success'    => true,
            'categories' => Category::where('status', 1)->orderBy('name')->get(['id', 'name'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]),
            'locations'  => Location::where('status', 1)->orderByDesc('id')->get(['id', 'name'])
                ->map(fn ($l) => ['id' => $l->id, 'name' => $l->name]),
        ]);
    }

    /**
     * Areas / city / state for a pincode — the "Address" step's Area dropdown.
     * Delegates to the same lookup the admin/web listing form uses (our own
     * listings' known areas + the postal API), so the app gets identical data.
     */
    public function pincode($pin)
    {
        return app(PincodeController::class)->show($pin);
    }

    /** Live search for the "Add Business Category" step. */
    public function searchSubcategories(Request $request)
    {
        $term = trim((string) $request->query('q'));

        if ($term === '') {
            return response()->json(['success' => true, 'results' => []]);
        }

        return response()->json([
            'success' => true,
            'results' => Subcategory::where('status', 1)
                ->where('name', 'like', '%' . $term . '%')
                ->orderBy('name')->take(20)->get(['id', 'name']),
        ]);
    }

    /**
     * Create a business. One call collects every wizard step — name/address,
     * contact, timings, category, photos — exactly like the website's
     * ListingController::store, which posts the whole form in one go. New
     * listings start unapproved (status 0), same as the web and free-listing
     * flows, so they wait for admin review before going live.
     */
    public function store(Request $request)
    {
        $request->validate([
            // Step 1 — name & address
            'name'                => 'required|string|max:255',
            'pincode'             => 'nullable|string|max:10',
            'plot_no'             => 'nullable|string|max:255',
            'building_name'       => 'nullable|string|max:255',
            'street_name'         => 'nullable|string|max:255',
            'landmark'            => 'nullable|string|max:255',
            'area'                => 'nullable|string|max:255',
            'city'                => 'nullable|string|max:255',
            'state'               => 'nullable|string|max:255',
            'location_id'         => 'required|exists:locations,id',
            'latitude'            => 'nullable|numeric',
            'longitude'           => 'nullable|numeric',

            // Step 2 — contact
            'contact_title'          => 'nullable|string|max:50',
            'contact_person'         => 'nullable|string|max:255',
            'mobile'                 => 'nullable|string|max:20',
            'email'                  => 'nullable|email|max:255',
            'mobile_country_code'    => 'nullable|array',
            'mobile_numbers'         => 'nullable|array',
            'mobile_numbers.*'       => 'nullable|string|max:20',
            'whatsapp_country_code'  => 'nullable|array',
            'whatsapp_numbers'       => 'nullable|array',
            'whatsapp_numbers.*'     => 'nullable|string|max:20',
            'landline_country_code'  => 'nullable|array',
            'landline_numbers'       => 'nullable|array',
            'landline_numbers.*'     => 'nullable|string|max:20',
            'extra_emails'           => 'nullable|array',
            'extra_emails.*'         => 'nullable|email|max:255',

            // Step 3 — timings & "since"
            'business_days'       => 'nullable|array',
            'business_days.*'     => 'nullable|string|max:20',
            'open_24_hours'       => 'nullable|boolean',
            'open_time'           => 'nullable|array',
            'open_time.*'         => 'nullable|string|max:50',
            'close_time'          => 'nullable|array',
            'close_time.*'        => 'nullable|string|max:50',
            'established_year'    => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),

            // Step 4 — category
            'category_id'         => 'required|exists:categories,id',
            'subcategory_ids'     => 'nullable|array',
            'subcategory_ids.*'   => 'nullable|exists:subcategories,id',

            // Step 5 — photos
            'thumb'               => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'photos'              => 'nullable|array',
            'photos.*'            => 'nullable|image|mimes:jpg,jpeg,png,webp',

            'details'             => 'nullable|string',
        ]);

        $user = $request->user();

        // Multi-value contact fields — filtered to drop the empty extra rows
        // the "Add Another …" buttons leave behind, same as the web form.
        $mobileCountryCode   = array_values(array_filter($request->mobile_country_code ?? []));
        $mobileNumbers       = array_values(array_filter($request->mobile_numbers ?? []));
        $whatsappCountryCode = array_values(array_filter($request->whatsapp_country_code ?? []));
        $whatsappNumbers     = array_values(array_filter($request->whatsapp_numbers ?? []));
        $landlineCountryCode = array_values(array_filter($request->landline_country_code ?? []));
        $landlineNumbers     = array_values(array_filter($request->landline_numbers ?? []));
        $extraEmails         = array_values(array_filter($request->extra_emails ?? []));
        $businessDays        = array_values(array_filter($request->business_days ?? []));
        $subcategoryIds      = array_values(array_filter($request->subcategory_ids ?? []));

        // Open 24 Hours overrides the per-slot times; otherwise each slot pair
        // is one shift ("Add Another Time Slot").
        if ($request->boolean('open_24_hours')) {
            $openTime  = ['12:00 AM'];
            $closeTime = ['11:59 PM'];
        } else {
            $openTime  = array_values(array_filter($request->open_time ?? []));
            $closeTime = array_values(array_filter($request->close_time ?? []));
        }

        // Photos / thumb — same on-disk layout the web form and the rest of
        // this API (loadImage paths) expect.
        $thumb = null;
        if ($request->hasFile('thumb')) {
            $file = $request->file('thumb');
            $thumb = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/listing/thumb'), $thumb);
        }

        $uploadedPhotos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo) {
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $photo->move(public_path('assets/images/listing/photos'), $filename);
                    $uploadedPhotos[] = $filename;
                }
            }
        }

        $businessCategories = $subcategoryIds
            ? Subcategory::whereIn('id', $subcategoryIds)->pluck('name')->toArray()
            : [];

        $listing = new Listing();
        $listing->location_id       = $request->location_id;
        $listing->name              = $request->name;
        $listing->slug              = Str::slug($request->name) . '-' . time();
        $listing->user_id           = $user->id;
        $listing->category_id       = $request->category_id;
        $listing->details           = $request->filled('details') ? (new HTMLPurifier())->purify($request->details) : null;
        $listing->plot_no           = $request->plot_no;
        $listing->building_name     = $request->building_name;
        $listing->street_name       = $request->street_name;
        $listing->landmark          = $request->landmark;
        $listing->thumb             = $thumb;
        $listing->is_featured       = 0;
        $listing->status            = 0;
        $listing->established_year   = $request->established_year;
        $listing->contact_title     = $request->contact_title;
        $listing->contact_person    = $request->contact_person;
        $listing->mobile_country_code   = $mobileCountryCode;
        $listing->mobile_numbers        = $mobileNumbers;
        $listing->whatsapp_country_code = $whatsappCountryCode;
        $listing->whatsapp_numbers      = $whatsappNumbers;
        $listing->landline_country_code = $landlineCountryCode;
        $listing->landline_numbers      = $landlineNumbers;
        $listing->extra_emails          = $extraEmails;
        $listing->subcategory_ids       = $subcategoryIds;
        $listing->business_days         = $businessDays;
        $listing->open_time             = $openTime;
        $listing->close_time            = $closeTime;
        $listing->business_categories   = $businessCategories;
        $listing->photos                = $uploadedPhotos;
        $listing->verified              = 0;
        $listing->trust                 = 0;

        // These columns exist on this install but are guarded on the web form
        // the same way — write them only when present.
        foreach (['mobile' => $request->mobile, 'email' => $request->email,
                  'pincode' => $request->pincode, 'area' => $request->area,
                  'city' => $request->city, 'state' => $request->state,
                  'latitude' => $request->latitude, 'longitude' => $request->longitude] as $col => $val) {
            if (Schema::hasColumn('listings', $col)) {
                $listing->{$col} = $val;
            }
        }

        $listing->save();

        // Same admin "new listing to review" ping the web create fires.
        event(new AdminNotification('listing', Listing::where('status', 0)->count()));

        return response()->json([
            'success'  => true,
            'message'  => 'Business added successfully. It will go live after admin approval.',
            'business' => $this->businessCard($listing->load('location', 'category')),
        ], 201);
    }

    /* ---------------------------------------------------------------------
     | Business dashboard — the enquiry-stat cards
     | -------------------------------------------------------------------*/

    public function dashboard(Request $request, $id)
    {
        $listing = $this->ownedListing($request, $id);

        // type='enquiry' only — quotation requests are counted on their own,
        // never folded into these numbers (same split as the website).
        $enquiries = Enquiry::where('listing_id', $listing->id)->where('type', 'enquiry');

        $total    = (clone $enquiries)->count();
        $today    = (clone $enquiries)->whereDate('created_at', today())->count();
        $missed   = (clone $enquiries)->where('is_read', 0)->count();
        $responded = $total - $missed;
        $hot      = (clone $enquiries)->where('created_at', '>=', now()->subDay())->count();

        $quotation = Enquiry::where('listing_id', $listing->id)
            ->where('type', 'quotation')
            ->where('is_hidden', 0)
            ->count();

        return response()->json([
            'success'  => true,
            'business' => $this->businessCard($listing),
            'stats'    => [
                'today_enquiries'   => $today,
                'missed_enquiries'  => $missed,
                'total_enquiries'   => $total,
                'responded'         => $responded,
                'responded_percent' => $total > 0 ? round(($responded / $total) * 100) : 0,
                'hot_enquiries'     => $hot,
                'quotation_requests'=> $quotation,
            ],
            'recent_enquiries' => (clone $enquiries)->latest()->take(10)->get()
                ->map(fn ($e) => $this->enquiryPayload($e))->values(),
        ]);
    }

    /* ---------------------------------------------------------------------
     | Leads / Enquiries — the filterable list (and Quotation Request)
     | -------------------------------------------------------------------*/

    public function leads(Request $request, $id)
    {
        $listing = $this->ownedListing($request, $id);

        // One method, two screens: plain enquiries (default) and Quotation
        // Request (type=quotation) — same filters, different base slice.
        $type = $request->query('type') === 'quotation' ? 'quotation' : 'enquiry';

        $base = Enquiry::where('listing_id', $listing->id)->where('type', $type);

        $totalCount = (clone $base)->where('is_hidden', 0)->count();
        $newCount   = (clone $base)->where('is_hidden', 0)->where('is_read', 0)->count();

        $query = (clone $base)->with('category');

        // Filter tabs — same cuts as the website.
        switch ($request->query('filter')) {
            case 'hidden':  $query->where('is_hidden', 1); break;
            case 'starred': $query->where('is_starred', 1); break;
            case 'notes':   $query->where('is_hidden', 0)->whereNotNull('note')->where('note', '!=', ''); break;
            case 'hot':     $query->where('is_hidden', 0)->where('created_at', '>=', now()->subDay()); break;
            case 'today':   $query->where('is_hidden', 0)->whereDate('created_at', today()); break;
            case 'new':
            case 'missed':  $query->where('is_hidden', 0)->where('is_read', 0); break;
            default:        $query->where('is_hidden', 0);
        }

        // Category (subcategory) filter — enquiries carry a top-level
        // category_id, matched to the listing's subcategories by name.
        if ($request->filled('subcategory_id')) {
            $names = Subcategory::whereIn('id', (array) $request->subcategory_id)->pluck('name');
            $query->whereHas('category', fn ($q) => $q->whereIn('name', $names));
        }

        // Date range.
        switch ($request->query('date_range')) {
            case 'today':     $query->whereDate('created_at', today()); break;
            case 'yesterday': $query->whereDate('created_at', today()->subDay()); break;
            case 'week':      $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]); break;
            case 'month':     $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]); break;
            case 'custom':
                if ($request->filled('date_from') && $request->filled('date_to')) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($request->date_from)->startOfDay(),
                        Carbon::parse($request->date_to)->endOfDay(),
                    ]);
                }
                break;
        }

        // Read/unread — the "Enquiry Status" dropdown.
        if ($request->query('status') === 'read') {
            $query->where('is_read', 1);
        } elseif ($request->query('status') === 'unread') {
            $query->where('is_read', 0);
        }

        $leads = $query->latest()->paginate(20)->withQueryString();

        // For quotations, "also sent to N other businesses" is a real count.
        $alsoSent = [];
        if ($type === 'quotation') {
            foreach ($leads as $lead) {
                $alsoSent[$lead->id] = Enquiry::where('type', 'quotation')
                    ->where('phone', $lead->phone)
                    ->where('category_id', $lead->category_id)
                    ->where('created_at', $lead->created_at)
                    ->where('listing_id', '!=', $listing->id)
                    ->count();
            }
        }

        return response()->json([
            'success'      => true,
            'type'         => $type,
            'total_count'  => $totalCount,
            'new_count'    => $newCount,
            'missed_count' => $newCount,
            // The subcategories the owner picked — the "Category" filter options.
            'category_options' => collect($listing->subcategory)->map(fn ($s) => [
                'id' => $s->id, 'name' => $s->name,
            ])->values(),
            'leads' => collect($leads->items())->map(function ($e) use ($alsoSent) {
                $row = $this->enquiryPayload($e);
                if (array_key_exists($e->id, $alsoSent)) {
                    $row['also_sent_to'] = $alsoSent[$e->id];
                }
                return $row;
            })->values(),
            'meta' => [
                'current_page' => $leads->currentPage(),
                'last_page'    => $leads->lastPage(),
                'total'        => $leads->total(),
            ],
        ]);
    }

    public function starLead(Request $request, $enquiryId)
    {
        $enquiry = $this->ownedEnquiry($request, $enquiryId);
        $enquiry->is_starred = !$enquiry->is_starred;
        $enquiry->save();

        return response()->json(['success' => true, 'starred' => $enquiry->is_starred]);
    }

    public function hideLead(Request $request, $enquiryId)
    {
        $enquiry = $this->ownedEnquiry($request, $enquiryId);
        $enquiry->is_hidden = !$enquiry->is_hidden;
        $enquiry->save();

        return response()->json(['success' => true, 'hidden' => $enquiry->is_hidden]);
    }

    public function noteLead(Request $request, $enquiryId)
    {
        $request->validate(['note' => 'nullable|string|max:2000']);

        $enquiry = $this->ownedEnquiry($request, $enquiryId);
        $enquiry->note = $request->note;
        $enquiry->save();

        return response()->json(['success' => true, 'note' => $enquiry->note]);
    }

    public function reminderLead(Request $request, $enquiryId)
    {
        $request->validate(['reminder_at' => 'required|date']);

        $enquiry = $this->ownedEnquiry($request, $enquiryId);
        $enquiry->reminder_at = $request->reminder_at;
        $enquiry->save();

        return response()->json(['success' => true, 'reminder_at' => $enquiry->reminder_at->toIso8601String()]);
    }

    /** Opening an enquiry marks it read and stamps the response time. */
    public function readLead(Request $request, $enquiryId)
    {
        $enquiry = $this->ownedEnquiry($request, $enquiryId);

        if (!$enquiry->is_read) {
            $enquiry->is_read = 1;
            $enquiry->responded_at = now();
            $enquiry->save();
        }

        return response()->json(['success' => true]);
    }

    /* ---------------------------------------------------------------------
     | Analytics
     | -------------------------------------------------------------------*/

    public function analytics(Request $request, $id)
    {
        $listing = $this->ownedListing($request, $id);

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());

        $enquiryRows = Enquiry::where('listing_id', $listing->id)->where('type', 'enquiry')
            ->where('created_at', '>=', $months->first())->get();
        $statRows = ListingDailyStats::where('listing_id', $listing->id)
            ->where('date', '>=', $months->first())->get();
        $ratingRows = $listing->ratings()->where('created_at', '>=', $months->first())->get();

        $monthly = $months->map(function ($month) use ($enquiryRows, $statRows, $ratingRows) {
            $inMonth = fn ($d) => $d->isSameMonth($month) && $d->isSameYear($month);
            $me = $enquiryRows->filter(fn ($e) => $inMonth($e->created_at));
            $ms = $statRows->filter(fn ($s) => $inMonth($s->date));

            return [
                'label'             => $month->format('M'),
                'enquiries'         => $me->count(),
                'responded'         => $me->where('is_read', 1)->count(),
                'views'             => (int) $ms->sum('views'),
                'phone_clicks'      => (int) $ms->sum('phone_clicks'),
                'whatsapp_clicks'   => (int) $ms->sum('whatsapp_clicks'),
                'direction_clicks'  => (int) $ms->sum('direction_clicks'),
                'gallery_clicks'    => (int) $ms->sum('gallery_clicks'),
                'reviews'           => $ratingRows->filter(fn ($r) => $inMonth($r->created_at))->count(),
            ];
        });

        $totals = [
            'enquiries'        => $enquiryRows->count(),
            'responded'        => $enquiryRows->where('is_read', 1)->count(),
            'views'            => (int) $statRows->sum('views'),
            'unique_views'     => (int) $statRows->sum('unique_views'),
            'phone_clicks'     => (int) $statRows->sum('phone_clicks'),
            'whatsapp_clicks'  => (int) $statRows->sum('whatsapp_clicks'),
            'website_clicks'   => (int) $statRows->sum('website_clicks'),
            'direction_clicks' => (int) $statRows->sum('direction_clicks'),
            'gallery_clicks'   => (int) $statRows->sum('gallery_clicks'),
            'share_clicks'     => (int) $statRows->sum('share_clicks'),
            'favourite_clicks' => (int) $statRows->sum('favourite_clicks'),
        ];
        $totals['responded_percent'] = $totals['enquiries'] > 0
            ? round(($totals['responded'] / $totals['enquiries']) * 100) : 0;

        return response()->json([
            'success'       => true,
            'business'      => $this->businessCard($listing),
            'monthly'       => $monthly,
            'totals'        => $totals,
            'total_reviews' => $listing->ratings()->count(),
            'avg_rating'    => round($listing->ratings()->avg('ratings') ?? 0, 1),
        ]);
    }

    /** Average response time — only from enquiries that carry a responded_at. */
    public function responseTime(Request $request, $id)
    {
        $listing = $this->ownedListing($request, $id);

        $weeks = collect(range(3, 0))->map(fn ($i) => now()->subWeeks($i)->startOfWeek());

        $responded = Enquiry::where('listing_id', $listing->id)->where('type', 'enquiry')
            ->whereNotNull('responded_at')
            ->where('created_at', '>=', $weeks->first())->get();

        $weekly = $weeks->map(function ($start) use ($responded) {
            $end = $start->copy()->endOfWeek();
            $inWeek = $responded->filter(fn ($e) => $e->created_at->between($start, $end));

            return [
                'label'       => $start->format('d M'),
                'avg_seconds' => $inWeek->isNotEmpty()
                    ? round($inWeek->avg(fn ($e) => $e->responded_at->diffInSeconds($e->created_at)))
                    : null,
                'count'       => $inWeek->count(),
            ];
        });

        return response()->json([
            'success'             => true,
            'business'            => $this->businessCard($listing),
            'weekly'              => $weekly,
            'overall_avg_seconds' => $responded->isNotEmpty()
                ? round($responded->avg(fn ($e) => $e->responded_at->diffInSeconds($e->created_at)))
                : null,
            'measured_count'      => $responded->count(),
        ]);
    }

    /** Competitors — ranks this listing against real peers in its category+city. */
    public function competitors(Request $request, $id)
    {
        $listing = $this->ownedListing($request, $id);

        $peers = Listing::where('category_id', $listing->category_id)
            ->where('location_id', $listing->location_id)
            ->where('status', 1)->notExpired()->get();

        if (!$peers->contains('id', $listing->id)) {
            $peers->push($listing);
        }

        $peerIds = $peers->pluck('id');

        $enquiryCounts = Enquiry::whereIn('listing_id', $peerIds)->where('type', 'enquiry')
            ->selectRaw('listing_id, count(*) as total')->groupBy('listing_id')->pluck('total', 'listing_id');
        $respondedCounts = Enquiry::whereIn('listing_id', $peerIds)->where('type', 'enquiry')->where('is_read', 1)
            ->selectRaw('listing_id, count(*) as total')->groupBy('listing_id')->pluck('total', 'listing_id');
        $ratingStats = Rating::whereIn('listing_id', $peerIds)
            ->selectRaw('listing_id, count(*) as cnt, avg(ratings) as avg_rating')
            ->groupBy('listing_id')->get()->keyBy('listing_id');
        $photoCounts = ExtraImage::whereIn('listing_id', $peerIds)
            ->selectRaw('listing_id, count(*) as total')->groupBy('listing_id')->pluck('total', 'listing_id');

        $ranking = $peers->map(function ($peer) use ($enquiryCounts, $respondedCounts, $ratingStats, $photoCounts) {
            $enq = (int) ($enquiryCounts[$peer->id] ?? 0);
            $res = (int) ($respondedCounts[$peer->id] ?? 0);

            return [
                'id'               => $peer->id,
                'name'             => $peer->name,
                'leads'            => $enq,
                'response_percent' => $enq > 0 ? round(($res / $enq) * 100) : null,
                'review_count'     => (int) ($ratingStats[$peer->id]->cnt ?? 0),
                'avg_rating'       => round((float) ($ratingStats[$peer->id]->avg_rating ?? 0), 1),
                'photo_count'      => (int) ($photoCounts[$peer->id] ?? 0),
            ];
        })->sortByDesc('leads')->values()
          ->map(function ($row, $i) use ($listing) {
              $row['rank'] = $i + 1;
              $row['is_self'] = $row['id'] === $listing->id;
              return $row;
          });

        $others = $ranking->where('id', '!=', $listing->id);

        return response()->json([
            'success' => true,
            'self'    => $ranking->firstWhere('id', $listing->id),
            'ranking' => $ranking->values(),
            'competition_averages' => [
                'response_percent' => $others->pluck('response_percent')->filter(fn ($v) => $v !== null)->avg(),
                'avg_rating'       => $others->pluck('avg_rating')->filter()->avg(),
                'review_count'     => round($others->pluck('review_count')->avg() ?? 0, 1),
                'photo_count'      => round($others->pluck('photo_count')->avg() ?? 0, 1),
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Import leads — CSV / Excel upload
     | -------------------------------------------------------------------*/

    public function importLeads(Request $request, $id)
    {
        $listing = $this->ownedListing($request, $id);

        $request->validate([
            'data_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        $rows  = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('data_file'));
        $sheet = $rows[0] ?? [];

        if (count($sheet) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'That file has no data rows to import.',
            ], 422);
        }

        $header = array_map(fn ($c) => strtolower(trim((string) $c)), $sheet[0]);
        $dateCol = $mobileCol = $nameCol = null;

        foreach ($header as $i => $label) {
            if ($dateCol === null && str_contains($label, 'date')) $dateCol = $i;
            if ($mobileCol === null && (str_contains($label, 'mobile') || str_contains($label, 'phone'))) $mobileCol = $i;
            if ($nameCol === null && str_contains($label, 'name')) $nameCol = $i;
        }

        if ($dateCol === null || $mobileCol === null) {
            return response()->json([
                'success' => false,
                'message' => 'The file must have an "Enquiry Date" column and a "Mobile Number" column.',
            ], 422);
        }

        $imported = $skipped = 0;
        $oneYearAgo = now()->subYear();

        foreach (array_slice($sheet, 1) as $row) {
            $phone   = trim((string) ($row[$mobileCol] ?? ''));
            $rawDate = trim((string) ($row[$dateCol] ?? ''));

            if ($phone === '' || $rawDate === '') { $skipped++; continue; }

            try {
                $date = Carbon::parse($rawDate);
            } catch (\Throwable $e) {
                $skipped++;
                continue;
            }

            // "Only 1 year data from current day can be viewed."
            if ($date->lt($oneYearAgo)) { $skipped++; continue; }

            Enquiry::create([
                'listing_id' => $listing->id,
                'name'       => $nameCol !== null ? (trim((string) ($row[$nameCol] ?? '')) ?: 'Imported Lead') : 'Imported Lead',
                'phone'      => $phone,
                'is_read'    => 0,
                'created_at' => $date,
                'updated_at' => now(),
            ]);

            $imported++;
        }

        return response()->json([
            'success'  => true,
            'message'  => "Imported {$imported} lead(s)." . ($skipped ? " {$skipped} row(s) skipped (missing/invalid date or mobile)." : ''),
            'imported' => $imported,
            'skipped'  => $skipped,
        ]);
    }

    /* ---------------------------------------------------------------------
     | Business Tools
     | -------------------------------------------------------------------*/

    /**
     * The real tools a business owner has: their public profile / review link
     * (also the data to render a Ratings QR client-side), a ready-made share
     * message, and the Universal Business Listing item.
     *
     * Universal Listing (sync to Justdial / Google) is reported honestly as
     * not-yet-available rather than faked — a real sync needs those platforms'
     * own APIs. The owner can register interest (requestUniversalListing) and
     * the team follows up.
     */
    public function tools(Request $request, $id)
    {
        $listing = $this->ownedListing($request, $id);

        $profileUrl = ($listing->category && $listing->slug)
            ? route('listings.details', [$listing->category->slug, $listing->slug])
            : url('/');

        $shareText = 'Check out ' . $listing->name . ' on Service Bazzar: ' . $profileUrl;

        // Already asked for Universal Listing?
        $requested = AppFeedback::where('user_id', $request->user()->id)
            ->where('type', 'universal_listing')
            ->where('message', 'like', '%listing #' . $listing->id . '%')
            ->exists();

        return response()->json([
            'success' => true,
            'tools'   => [
                'profile_url'   => $profileUrl,
                'review_url'    => $profileUrl,
                // Encode this string into a QR on the device (no server QR needed).
                'ratings_qr_data' => $profileUrl,
                'share_text'    => $shareText,
                'whatsapp_share'=> 'https://wa.me/?text=' . rawurlencode($shareText),
                'universal_listing' => [
                    'available'   => false,
                    'requested'   => $requested,
                    'title'       => 'Universal Business Listing',
                    'description' => 'Manage your listing on Justdial and Google from one place. Request it and our team will set it up for you.',
                ],
            ],
        ]);
    }

    /** Register interest in the Universal Business Listing sync. */
    public function requestUniversalListing(Request $request, $id)
    {
        $listing = $this->ownedListing($request, $id);

        AppFeedback::create([
            'user_id' => $request->user()->id,
            'type'    => 'universal_listing',
            'subject' => 'Universal Business Listing request',
            'message' => 'Sync request for listing #' . $listing->id . ' (' . $listing->name . ')',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanks! Our team will reach out to set up your Universal Business Listing.',
        ]);
    }

    /* ---------------------------------------------------------------------
     | Helpers
     | -------------------------------------------------------------------*/

    /** A listing the caller owns, or 404 — never another owner's business. */
    protected function ownedListing(Request $request, $id): Listing
    {
        return Listing::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
    }

    /** An enquiry on one of the caller's listings, or 404. */
    protected function ownedEnquiry(Request $request, $enquiryId): Enquiry
    {
        return Enquiry::whereHas('listing', fn ($q) => $q->where('user_id', $request->user()->id))
            ->findOrFail($enquiryId);
    }

    protected function businessCard(Listing $listing): array
    {
        return [
            'id'           => $listing->id,
            'name'         => $listing->name,
            'slug'         => $listing->slug,
            'area'         => optional($listing->location)->name,
            'category'     => optional($listing->category)->name,
            'thumb'        => $listing->thumb
                ? loadImage('assets/images/listing/thumb/' . $listing->thumb)
                : loadImage('', false),
            'status'       => (int) $listing->status,
            'featured'     => (bool) $listing->featured,
            'avg_rating'   => round((float) ($listing->ratings()->avg('ratings') ?? 0), 1),
            'review_count' => (int) $listing->ratings()->count(),
            // Real profile-completeness, not a fabricated engagement score:
            // the share of key profile sections this listing has actually
            // filled in. This is what the app's "Profile Score" bar reflects.
            'profile_score' => $this->profileCompleteness($listing),
        ];
    }

    /**
     * Percentage of key profile sections filled in — 10 real signals, each
     * worth 10%. Uses only columns the listing genuinely has, so the number
     * is honest and improves as the owner completes their profile.
     */
    protected function profileCompleteness(Listing $listing): int
    {
        $filled = fn ($v) => is_array($v) ? count(array_filter($v)) > 0 : filled($v);

        $signals = [
            $filled($listing->thumb),
            $filled($listing->photos),
            $filled($listing->mobile_numbers),
            $filled($listing->whatsapp_numbers),
            $filled($listing->city) || $filled($listing->area) || $filled($listing->address),
            $filled($listing->latitude) && $filled($listing->longitude),
            $filled($listing->business_days),
            $filled($listing->established_year),
            $filled($listing->subcategory_ids),
            $filled($listing->faq) || $filled($listing->social_links) || $filled($listing->features),
        ];

        return (int) round((collect($signals)->filter()->count() / count($signals)) * 100);
    }

    protected function enquiryPayload(Enquiry $e): array
    {
        return [
            'id'           => $e->id,
            'type'         => $e->type,
            'name'         => $e->name,
            'phone'        => $e->phone,
            'email'        => $e->email,
            'message'      => $e->message,
            'category'     => optional($e->category)->name,
            'is_read'      => (bool) $e->is_read,
            'is_starred'   => (bool) $e->is_starred,
            'is_hidden'    => (bool) $e->is_hidden,
            'note'         => $e->note,
            'reminder_at'  => optional($e->reminder_at)->toIso8601String(),
            'responded_at' => optional($e->responded_at)->toIso8601String(),
            'created_at'   => optional($e->created_at)->toIso8601String(),
        ];
    }
}
