<?php

namespace App\Http\Controllers;

use App\Models\SalesUser;
use App\Services\WhatsappSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SalesGenioController extends Controller
{
    /** Where the still frame for each uploaded video is kept. */
    private const VIDEO_POSTER_DIR = 'assets/images/listing/videos/posters';

    public function showLogin()
    {
        if (Auth::guard('sales')->check()) {
            return redirect()->route('sales_genio.dashboard');
        }
        return view('sales_genio.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'empcode'  => 'required',
            'password' => 'required',
        ]);

        $user = SalesUser::where('empcode', strtoupper(trim($request->empcode)))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['empcode' => 'Invalid Employee Code or Password.'])->withInput();
        }

        if ($user->status != 1) {
            return back()->withErrors(['empcode' => 'Your account has been deactivated.']);
        }

        // The OTP is skipped only on a browser that has already passed one for
        // this rep. It used to be skipped whenever last_verified_at was under
        // 24 hours old — a fact about the account, not the device — so an
        // incognito window, or anyone else holding the password, got straight
        // in without an OTP for the rest of the day.
        if (\App\Models\SalesTrustedDevice::trusted($request, $user)) {
            // No "remember me": that cookie outlived the session by years and
            // was why a rep never saw the login screen again.
            Auth::guard('sales')->login($user);
            $request->session()->regenerate();
            $request->session()->put('sales_last_seen', now()->timestamp);

            return redirect()->route('sales_genio.dashboard');
        }

        // Need OTP — generate and send via WhatsApp
        if (!$user->phone) {
            return back()->withErrors(['empcode' => 'No WhatsApp number on your account. Contact admin.']);
        }

        $otp = (string) random_int(100000, 999999);

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send via WhatsApp
        $this->sendOtpWhatsapp($user, $otp);

        // Store empcode in session for OTP step
        $request->session()->put('sales_pending_empcode', $user->empcode);

        return redirect()->route('sales_genio.otp')
            ->with('otp_sent', true)
            ->with('phone_hint', $this->maskPhone($user->phone));
    }

    public function showOtp(Request $request)
    {
        if (!$request->session()->has('sales_pending_empcode')) {
            return redirect()->route('sales_genio.login');
        }
        return view('sales_genio.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $empcode = $request->session()->get('sales_pending_empcode');

        if (!$empcode) {
            return redirect()->route('sales_genio.login');
        }

        $user = SalesUser::where('empcode', $empcode)->first();

        if (!$user || $user->otp !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        if ($user->otp_expires_at && $user->otp_expires_at->isPast()) {
            return back()->withErrors(['otp' => 'OTP has expired. Please login again.']);
        }

        // OTP correct — clear it, set verified time, log in
        $user->update([
            'otp'              => null,
            'otp_expires_at'   => null,
            'last_verified_at' => now(),
        ]);

        $request->session()->forget('sales_pending_empcode');

        // This browser has now proved itself, so it can skip the OTP next time.
        \App\Models\SalesTrustedDevice::remember($request, $user);

        Auth::guard('sales')->login($user);
        $request->session()->regenerate();
        $request->session()->put('sales_last_seen', now()->timestamp);

        return redirect()->route('sales_genio.dashboard');
    }

    public function dashboard()
    {
        $user   = Auth::guard('sales')->user();
        $cities = \App\Models\Location::where('status', 1)->orderBy('name')->pluck('name');
        $counts = self::leadCounts($user->id);
        $stats  = self::dashboardStats($user->id);

        return view('sales_genio.dashboard', compact('user', 'cities', 'counts', 'stats'));
    }

    /**
     * The rep's own month so far, for the cards at the top of the dashboard.
     * Everything here is real: deals and revenue come from payments received,
     * not from a ticked disposition.
     *
     * These are the rep's TOTAL for the month, across every city. The city
     * picker narrows the work queues, but the dashboard is the scoreboard —
     * switching city there made a rep's own figures drop to zero.
     */
    public static function dashboardStats(int $salesUserId): array
    {
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        $paid = \App\Models\SalesOrder::where('sales_user_id', $salesUserId)
            ->where('status', \App\Models\SalesOrder::PAID)
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        $newLeads = \App\Models\Listing::where('sales_user_id', $salesUserId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Still to be acted on: anything scheduled that is due by tonight, plus
        // the ones saved without a date.
        $followPending = \App\Models\ListingDisposition::where('sales_user_id', $salesUserId)
            ->whereIn('disposition', ['Follow Up', 'Call Back', 'Appointment Refixed', 'Self Appointment'])
            ->where(fn ($q) => $q->whereNull('due_at')->orWhere('due_at', '<=', now()->endOfDay()))
            ->count();

        // Businesses actually pitched this month — one row per business, however
        // many times the rep called it.
        $pitched = \App\Models\ListingDisposition::where('sales_user_id', $salesUserId)
            ->whereBetween('created_at', [$start, $end])
            ->distinct()->count('listing_id');

        $won = $paid->pluck('listing_id')->unique()->count();

        return [
            'deals'       => $paid->count(),
            'revenue'     => (float) $paid->sum('total'),
            'follow_ups'  => $followPending,
            'new_leads'   => $newLeads,
            'pitched'     => $pitched,
            'won'         => $won,
            'conversion'  => $pitched > 0 ? round($won / $pitched * 100) : null,
        ];
    }

    /** The rep's own account: name, contact, picture and password. */
    public function profile()
    {
        $user   = Auth::guard('sales')->user();
        $cities = \App\Models\Location::where('status', 1)->orderBy('name')->pluck('name');
        $counts = self::leadCounts($user->id);
        $stats  = self::dashboardStats($user->id);

        return view('sales_genio.profile', compact('user', 'cities', 'counts', 'stats'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('sales')->user();

        $request->validate([
            'name'  => 'required|string|max:120',
            'email' => 'nullable|email|max:191|unique:sales_users,email,' . $user->id,
            'phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'city'  => 'nullable|string|max:100',
        ], [
            'phone.regex'  => 'Enter a 10-digit Indian mobile number.',
            'email.unique' => 'Another employee already uses that email address.',
        ]);

        $user->update($request->only('name', 'email', 'phone', 'city'));

        return back()->with('success', 'Your details have been saved.');
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::guard('sales')->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'avatar.max' => 'The picture must be under 5MB.',
        ]);

        $dir = public_path(\App\Models\SalesUser::AVATAR_DIR);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            return back()->with('error', 'Could not save the picture. Try again.');
        }

        $old = $user->avatar;
        // A fresh name every time — assets are cached for a year by filename.
        $name = 'sg_' . $user->id . '_' . time() . '.webp';

        try {
            $this->resizeToWebp($request->file('avatar')->getRealPath(), $dir . '/' . $name, 400, 85);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Sales avatar upload failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Could not process that image. Try another one.');
        }

        $user->update(['avatar' => $name]);

        if ($old && is_file($dir . '/' . $old)) {
            @unlink($dir . '/' . $old);
        }

        return back()->with('success', 'Profile picture updated.');
    }

    public function removeAvatar()
    {
        $user = Auth::guard('sales')->user();
        $file = public_path(\App\Models\SalesUser::AVATAR_DIR . '/' . $user->avatar);

        if ($user->avatar && is_file($file)) {
            @unlink($file);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Profile picture removed.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('sales')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ], [
            'password.min'       => 'Choose a password of at least 8 characters.',
            'password.confirmed' => 'The two new passwords do not match.',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'That is not your current password.']);
        }

        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->password)]);

        return back()->with('success', 'Password changed.');
    }

    /**
     * The work queues behind the top menu: Follow Up, Call Back, Appointments,
     * Dispositions, New Business and Deal Closed — all of one sales user's own
     * work, newest first.
     */
    public function leads(Request $request, string $tab = 'follow-up')
    {
        $user = Auth::guard('sales')->user();

        $tabs = self::leadTabs();

        if (!isset($tabs[$tab])) {
            abort(404);
        }

        $meta = $tabs[$tab];
        $search = trim((string) $request->input('q'));
        $searchBy = $request->input('by', 'name');

        $city = self::activeCity();

        if ($tab === 'deal-closed') {
            // Real payments, not a manually ticked disposition.
            $rows = \App\Models\SalesOrder::query()
                ->where('sales_user_id', $user->id)
                ->where('status', \App\Models\SalesOrder::PAID)
                ->when($city, fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('city', $city)))
                ->when($search, fn ($q) => $q->whereHas('listing', fn ($w) => self::applySearch($w, $searchBy, $search)))
                ->with(['listing', 'items'])
                ->latest('paid_at')
                ->paginate(20)->withQueryString();
        } elseif ($tab === 'all-business') {
            // Every business on the site — old listings included — so a rep can
            // find one and edit it. Needs something to search on.
            $rows = \App\Models\Listing::query()
                ->when($city, fn ($q) => $q->where('city', $city))
                ->when($search, fn ($q) => self::applySearch($q, $searchBy, $search))
                ->when(!$search && !$city, fn ($q) => $q->whereRaw('1 = 0'))
                ->with('category:id,name')
                ->latest('id')
                ->paginate(20)->withQueryString();
        } elseif ($tab === 'new-business') {
            // Same two-pane layout as Follow Up, so the filters work on the
            // date the business was added rather than on a due date.
            $rows = \App\Models\Listing::query()
                ->where('sales_user_id', $user->id)
                ->when($city, fn ($q) => $q->where('city', $city))
                ->when($search, fn ($q) => self::applySearch($q, $searchBy, $search))
                ->when($request->input('due') === 'today',
                       fn ($q) => $q->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]))
                ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
                ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
                ->when($request->filled('area'), fn ($q) => $q->where('area', 'like', '%' . $request->input('area') . '%'))
                ->when($request->filled('pincode'), fn ($q) => $q->where('pincode', $request->input('pincode')))
                ->with('category:id,name')
                ->latest('id')
                ->paginate(30)->withQueryString();
        } else {
            $scheduled = (bool) array_intersect(
                $meta['dispositions'] ?: [],
                \App\Models\ListingDisposition::SCHEDULED
            );

            $rows = \App\Models\ListingDisposition::query()
                ->where('sales_user_id', $user->id)
                ->when(!empty($meta['dispositions']), fn ($q) => $q->whereIn('disposition', $meta['dispositions']))
                ->when(!empty($meta['call_back']), fn ($q) => $q->where('call_back', 1))
                ->when($city, fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('city', $city)))
                ->when($search, fn ($q) => $q->whereHas('listing', fn ($w) => self::applySearch($w, $searchBy, $search)))
                ->when($scheduled, fn ($q) => self::applyDueFilter($q, (string) $request->input('due', 'all')))
                ->when($scheduled && ($request->filled('from') || $request->filled('to')),
                       fn ($q) => self::applyDueRange($q, $request->input('from'), $request->input('to')))
                ->when($request->filled('area'), fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('area', 'like', '%' . $request->input('area') . '%')))
                ->when($request->filled('pincode'), fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('pincode', $request->input('pincode'))))
                ->with('listing')
                // Anything with a due date comes first, soonest at the top.
                ->when($scheduled, fn ($q) => $q->orderByRaw('due_at IS NULL')->orderBy('due_at'),
                       fn ($q) => $q->latest())
                ->paginate($scheduled ? 30 : 20)->withQueryString();
        }

        $counts = self::leadCounts($user->id);
        $due = self::dueCounts($user->id, $meta['dispositions'] ?? []);

        // The two-pane layout: the list on the left, the business the rep is
        // working on open beside it.
        $isScheduled = !empty($meta['dispositions'])
            && (bool) array_intersect($meta['dispositions'], \App\Models\ListingDisposition::SCHEDULED);

        if ($isScheduled || $tab === 'new-business') {
            $open = (int) $request->input('open');
            $first = $rows->first();

            if (!$open && $first) {
                // New Business lists the businesses themselves; the scheduled
                // queues list dispositions that point at one.
                $open = (int) ($tab === 'new-business' ? $first->id : $first->listing_id);
            }

            $detail = $open ? self::businessDetail($open) : null;
            $dueFilter = (string) $request->input('due', 'all');

            return view('sales_genio.followup', compact(
                'user', 'rows', 'tab', 'tabs', 'meta', 'counts', 'search', 'searchBy',
                'due', 'city', 'detail', 'open', 'dueFilter'
            ));
        }

        return view('sales_genio.leads', compact('user', 'rows', 'tab', 'tabs', 'meta', 'counts', 'search', 'searchBy', 'due', 'city'));
    }

    /** Live suggestions for the top search box. */
    public function searchSuggest(Request $request)
    {
        $term = trim((string) $request->query('q'));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $city = self::activeCity();
        $user = Auth::guard('sales')->user();

        // On a queue page the box searches that queue, so the suggestions must
        // come from it too — otherwise it offers businesses that are not there.
        $tab  = (string) $request->query('tab');
        $tabs = self::leadTabs();
        $queue = ($tab && $tab !== 'all-business' && isset($tabs[$tab])) ? $tabs[$tab] : null;

        $rows = \App\Models\Listing::query()
            ->when($city, fn ($q) => $q->where('city', $city))
            ->when($queue, fn ($q) => $this->scopeToQueue($q, $tab, $queue, $user->id))
            ->where(fn ($q) => self::applySearch($q, $request->query('by', 'name'), $term))
            ->with('category:id,name')
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$term . '%'])
            ->limit(8)
            ->get(['id', 'name', 'area', 'city', 'mobile', 'category_id']);

        return response()->json($rows->map(fn ($l) => [
            'id'    => $l->id,
            'name'  => $l->name,
            'place' => trim(($l->area ? $l->area . ', ' : '') . $l->city, ', '),
            'phone' => $l->mobile,
            'cat'   => optional($l->category)->name,
            'url'   => route('sales_genio.business.profile', $l->id),
        ]));
    }

    /** The "All / Todays" switch above the list. */
    public static function applyDueFilter($query, string $due)
    {
        return match ($due) {
            'today'    => $query->whereNotNull('due_at')->whereBetween('due_at', [now()->startOfDay(), now()->endOfDay()]),
            'overdue'  => $query->whereNotNull('due_at')->where('due_at', '<', now()->startOfDay()),
            'upcoming' => $query->whereNotNull('due_at')->where('due_at', '>', now()->endOfDay()),
            'none'     => $query->whereNull('due_at'),
            default    => $query,
        };
    }

    /** The date range picked in the Filters panel, applied to the due date. */
    public static function applyDueRange($query, $from, $to)
    {
        $parse = function ($value) {
            try {
                return $value ? \Carbon\Carbon::parse($value) : null;
            } catch (\Throwable $e) {
                return null;
            }
        };

        $start = $parse($from);
        $end   = $parse($to);

        return $query->whereNotNull('due_at')
            ->when($start, fn ($q) => $q->where('due_at', '>=', $start->startOfDay()))
            ->when($end, fn ($q) => $q->where('due_at', '<=', $end->endOfDay()));
    }

    /**
     * Everything the panel beside the Follow Up list shows about one business:
     * who they are, what they have bought, what was paid, and where the last
     * conversation left off.
     */
    public static function businessDetail(int $listingId): ?array
    {
        $listing = \App\Models\Listing::with('category:id,name')->find($listingId);

        if (!$listing) {
            return null;
        }

        $packages = \App\Models\ListingSalesPackage::where('listing_id', $listing->id)
            ->with('package:id,name')
            ->orderByDesc('starts_at')
            ->get();

        $orders = \App\Models\SalesOrder::where('listing_id', $listing->id)
            ->where('status', \App\Models\SalesOrder::PAID)
            ->with('items')
            ->latest('paid_at')
            ->get();

        $last = \App\Models\ListingDisposition::where('listing_id', $listing->id)
            ->with('salesUser:id,name,empcode')
            ->latest('updated_at')
            ->first();

        // How much of the profile is filled in — the same checks that gate a
        // campaign, shown as a percentage.
        [$required, $optional] = self::campaignChecklist($listing);
        $all  = array_merge($required, $optional);
        $done = collect($all)->where('done', true)->count();

        $history = \App\Models\ListingDisposition::where('listing_id', $listing->id)
            ->with('salesUser:id,name,empcode')
            ->latest('updated_at')
            ->limit(20)->get();

        $ratings = $listing->ratings()->selectRaw('COUNT(*) as c, AVG(ratings) as a')->first();

        return [
            'listing'    => $listing,
            'packages'   => $packages,
            'orders'     => $orders,
            'revenue'    => (float) $orders->sum('total'),
            'last'       => $last,
            'history'    => $history,
            'rating'     => round((float) ($ratings->a ?? 0), 1),
            'rating_cnt' => (int) ($ratings->c ?? 0),
            'strength'   => count($all) ? (int) round($done / count($all) * 100) : 0,
            'missing'    => collect($required)->where('done', false)->pluck('label')->all(),
        ];
    }

    /** Narrows a Listing query to the businesses sitting in one work queue. */
    private function scopeToQueue($query, string $tab, array $meta, int $salesUserId)
    {
        if ($tab === 'deal-closed') {
            return $query->whereIn('id', \App\Models\SalesOrder::where('sales_user_id', $salesUserId)
                ->where('status', \App\Models\SalesOrder::PAID)->select('listing_id'));
        }

        if ($tab === 'new-business') {
            return $query->where('sales_user_id', $salesUserId);
        }

        return $query->whereIn('id', \App\Models\ListingDisposition::where('sales_user_id', $salesUserId)
            ->when(!empty($meta['dispositions']), fn ($q) => $q->whereIn('disposition', $meta['dispositions']))
            ->when(!empty($meta['call_back']), fn ($q) => $q->where('call_back', 1))
            ->select('listing_id'));
    }

    /** The fields a rep can search a business by, in menu order. */
    public static function searchFields(): array
    {
        return [
            'contract_id' => 'Contract ID',
            'name'        => 'Company Name',
            'keyword'     => 'Business Keyword',
            'area'        => 'Area',
            'pincode'     => 'Pincode',
            'mobile'      => 'Contact Number',
            'city'        => 'City',
        ];
    }

    /** Narrows a Listing query to one field, or across the lot by default. */
    public static function applySearch($query, ?string $by, string $term)
    {
        $like = '%' . $term . '%';

        return match ($by) {
            'contract_id' => $query->where('id', (int) filter_var($term, FILTER_SANITIZE_NUMBER_INT)),
            'area'        => $query->where('area', 'like', $like),
            'pincode'     => $query->where('pincode', 'like', $like),
            'city'        => $query->where('city', 'like', $like),
            'mobile'      => $query->where(fn ($w) => $w->where('mobile', 'like', $like)
                ->orWhere('mobile_numbers', 'like', $like)
                ->orWhere('whatsapp_numbers', 'like', $like)),
            'keyword'     => $query->where(fn ($w) => $w->where('meta_keywords', 'like', $like)
                ->orWhere('name', 'like', $like)
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like))),
            default       => $query->where(fn ($w) => $w->where('name', 'like', $like)
                ->orWhere('mobile', 'like', $like)),
        };
    }

    /** Each queue: its title and which dispositions feed it. */
    public static function leadTabs(): array
    {
        return [
            'follow-up'    => ['title' => 'Follow Up',    'dispositions' => ['Follow Up']],
            'call-back'    => ['title' => 'Call Back',    'dispositions' => ['Call Back'], 'call_back' => false],
            'appointments' => ['title' => 'Appointments', 'dispositions' => ['Appointment Refixed', 'Self Appointment']],
            'new-business' => ['title' => 'New Business', 'dispositions' => []],
            'all-business' => ['title' => 'All Businesses', 'dispositions' => []],
            'dispositions' => ['title' => 'Dispositions', 'dispositions' => []],
            'deal-closed'  => ['title' => 'Deal Closed',  'dispositions' => []],
            'not-interested' => ['title' => 'Not Interested', 'dispositions' => ['Not Interested', 'Do Not Disturb']],
            'pending-kyc'  => ['title' => 'Pending KYC',  'dispositions' => []],
        ];
    }

    /** How much of a queue is overdue, due today, or still ahead. */
    public static function dueCounts(int $salesUserId, array $dispositions): array
    {
        $city = self::activeCity();

        $base = \App\Models\ListingDisposition::where('sales_user_id', $salesUserId)
            ->when($dispositions, fn ($q) => $q->whereIn('disposition', $dispositions))
            ->when($city, fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('city', $city)));

        return [
            'overdue'  => (clone $base)->whereNotNull('due_at')->where('due_at', '<', now())->count(),
            'today'    => (clone $base)->whereNotNull('due_at')->whereDate('due_at', today())->count(),
            'upcoming' => (clone $base)->whereNotNull('due_at')->where('due_at', '>', now()->endOfDay())->count(),
            'undated'  => (clone $base)->whereNull('due_at')->count(),
        ];
    }

    /** Badge numbers on the menu. */
    public static function leadCounts(int $salesUserId): array
    {
        $city = self::activeCity();

        $byDisposition = \App\Models\ListingDisposition::where('sales_user_id', $salesUserId)
            ->when($city, fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('city', $city)))
            ->selectRaw('disposition, count(*) as c')->groupBy('disposition')
            ->pluck('c', 'disposition');

        $pick = fn (array $names) => collect($names)->sum(fn ($n) => (int) ($byDisposition[$n] ?? 0));

        return [
            'follow-up'      => $pick(['Follow Up']),
            'call-back'      => $pick(['Call Back']),
            'appointments'   => $pick(['Appointment Refixed', 'Self Appointment']),
            'dispositions'   => (int) $byDisposition->sum(),
            'deal-closed'    => \App\Models\SalesOrder::where('sales_user_id', $salesUserId)
                ->where('status', \App\Models\SalesOrder::PAID)
                ->when($city, fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('city', $city)))
                ->count(),
            'not-interested' => $pick(['Not Interested', 'Do Not Disturb']),
            'new-business'   => \App\Models\Listing::where('sales_user_id', $salesUserId)
                ->when($city, fn ($q) => $q->where('city', $city))->count(),
            'all-business'   => null,
            'pending-kyc'    => \App\Models\Listing::where('sales_user_id', $salesUserId)
                ->when($city, fn ($q) => $q->where('city', $city))
                ->whereDoesntHave('kycDocuments')->count(),
        ];
    }

    /** Month-to-date numbers plus a breakdown by disposition. */
    public function reports(Request $request)
    {
        $user = Auth::guard('sales')->user();

        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->from)->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->to)->endOfDay() : now()->endOfDay();

        $city = self::activeCity();

        $dispositions = \App\Models\ListingDisposition::where('sales_user_id', $user->id)
            ->when($city, fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('city', $city)))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('disposition, count(*) as c')->groupBy('disposition')
            ->orderByDesc('c')->pluck('c', 'disposition');

        $daily = \App\Models\Listing::where('sales_user_id', $user->id)
            ->when($city, fn ($q) => $q->where('city', $city))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as d, count(*) as c')->groupBy('d')
            ->orderBy('d')->pluck('c', 'd');

        // Closed deals come from payments received, not from a ticked box.
        $paidOrders = \App\Models\SalesOrder::where('sales_user_id', $user->id)
            ->where('status', \App\Models\SalesOrder::PAID)
            ->when($city, fn ($q) => $q->whereHas('listing', fn ($w) => $w->where('city', $city)))
            ->whereBetween('paid_at', [$from, $to])
            ->get();

        $deals = $paidOrders->count();
        $revenue = $paidOrders->sum('total');

        $counts = self::leadCounts($user->id);

        return view('sales_genio.reports', compact(
            'user', 'from', 'to', 'dispositions', 'daily', 'counts', 'city', 'deals', 'revenue'
        ));
    }

    /**
     * Deal Close Toolkit → Manage Campaign.
     *
     * A campaign can only be pitched on a profile that is actually complete:
     * anything required (contact, address, category, timings, photos, KYC) must
     * be filled first. The merely useful bits (backup number, email, website,
     * social, language) are offered in a popup the rep can skip.
     */
    public function manageCampaign(Request $request, $id)
    {
        $user = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);

        [$required, $optional] = self::campaignChecklist($business);

        if (collect($required)->where('done', false)->isNotEmpty()) {
            return redirect()->route('sales_genio.business.profile', $business->id)
                ->with('campaign_blocked', 1);
        }

        $packages = \App\Models\CertificatePackage::where('status', 1)->orderBy('id')->get();
        $skipped = (bool) $request->session()->get('campaign_optional_skipped_' . $business->id);

        // The package open in the right-hand panel.
        $selected = $request->filled('pkg')
            ? $packages->firstWhere('id', (int) $request->query('pkg'))
            : null;

        $preview = null;
        if ($selected) {
            $variant = \App\Support\SalesPreview::variantFor($selected);
            $preview = $variant ? \App\Support\SalesPreview::url($business, $variant) : null;
        }

        self::prunePaidCarts($request);
        $cart = collect($request->session()->get('campaign_cart_' . $business->id, []));

        return view('sales_genio.campaign', compact(
            'user', 'business', 'required', 'optional', 'packages', 'skipped', 'selected', 'preview', 'cart'
        ));
    }

    /**
     * Top-position flow, step 1 — the areas of the city the campaign should
     * run in, grouped by pincode. Only the business's own area is ticked to
     * begin with; "Select All" takes the whole city.
     */
    public function campaignAreas(Request $request, $id, $pkg)
    {
        $user = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $package = \App\Models\CertificatePackage::where('status', 1)->findOrFail($pkg);
        $packages = \App\Models\CertificatePackage::where('status', 1)->orderBy('id')->get();

        $groups = self::cityAreaGroups($business->city);
        $selected = $request->session()->get(self::areaKey($business->id, $package->id))
            ?? array_values(array_filter([self::ownPincode($business, $groups)]));

        return view('sales_genio.campaign_areas', compact('user', 'business', 'package', 'packages', 'groups', 'selected'));
    }

    public function saveCampaignAreas(Request $request, $id, $pkg)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate(['pincodes' => 'required|array|min:1', 'pincodes.*' => 'string|max:10']);

        $request->session()->put(self::areaKey($business->id, (int) $pkg), array_values($request->pincodes));

        return redirect()->route('sales_genio.business.campaign.position', ['id' => $business->id, 'pkg' => $pkg]);
    }

    /**
     * Step 2 — position, tenure and what the campaign costs across the areas
     * the rep picked.
     */
    public function campaignPosition(Request $request, $id, $pkg)
    {
        $user = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $package = \App\Models\CertificatePackage::where('status', 1)->findOrFail($pkg);
        $packages = \App\Models\CertificatePackage::where('status', 1)->orderBy('id')->get();

        $pincodes = $request->session()->get(self::areaKey($business->id, $package->id), []);

        if (!$pincodes) {
            return redirect()->route('sales_genio.business.campaign.areas', ['id' => $business->id, 'pkg' => $pkg]);
        }

        $groups = collect(self::cityAreaGroups($business->city))->whereIn('pincode', $pincodes)->values();
        $rates = self::tierRates($package, $business->city);
        $fullDays = (int) ($package->validity_days ?: 365);
        $tenures = self::tenureOptions($fullDays);

        // A slot someone else holds cannot be sold twice.
        $taken = self::takenSlots($business, $groups->pluck('pincode')->all());

        $categories = self::campaignCategories($business);

        return view('sales_genio.campaign_position', compact(
            'user', 'business', 'package', 'packages', 'groups', 'rates', 'fullDays', 'tenures', 'taken', 'categories'
        ));
    }

    /** Step 3 — hold the chosen positions and show the budget for approval. */
    public function saveCampaignPosition(Request $request, $id, $pkg)
    {
        $business = \App\Models\Listing::findOrFail($id);
        $package = \App\Models\CertificatePackage::where('status', 1)->findOrFail($pkg);

        $request->validate([
            'days'       => 'required|integer|min:1|max:3650',
            'position'   => 'required|array|min:1',
            'position.*' => 'nullable|string|max:60',
        ]);

        $picked = array_filter((array) $request->input('position', []), fn ($tier) => trim((string) $tier) !== '');

        if (!$picked) {
            return back()->with('error', 'Pick a position in at least one area.');
        }

        $request->session()->put(self::pickKey($business->id, $package->id), [
            'days'      => (int) $request->days,
            'positions' => $picked,
        ]);

        return redirect()->route('sales_genio.business.campaign.review', ['id' => $business->id, 'pkg' => $package->id]);
    }

    /** The budget review before anything reaches the pitch list. */
    public function campaignReview(Request $request, $id, $pkg)
    {
        $user = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $package = \App\Models\CertificatePackage::where('status', 1)->findOrFail($pkg);
        $packages = \App\Models\CertificatePackage::where('status', 1)->orderBy('id')->get();

        $quote = $this->buildQuote($request, $business, $package);

        if (!$quote) {
            return redirect()->route('sales_genio.business.campaign.position', ['id' => $business->id, 'pkg' => $package->id]);
        }

        $freebie = \App\Models\CertificatePackage::where('status', 1)
            ->where('perk_type', 'certificate')->orderBy('id')->first();

        return view('sales_genio.campaign_review', compact('user', 'business', 'package', 'packages', 'quote', 'freebie'));
    }

    /** Approved — the campaign and its free add-on go on the pitch list. */
    public function confirmCampaign(Request $request, $id, $pkg)
    {
        $business = \App\Models\Listing::findOrFail($id);
        $package = \App\Models\CertificatePackage::where('status', 1)->findOrFail($pkg);

        $quote = $this->buildQuote($request, $business, $package);

        if (!$quote) {
            return redirect()->route('sales_genio.business.campaign.position', ['id' => $business->id, 'pkg' => $package->id]);
        }

        if (!empty($quote['clash'])) {
            return redirect()->route('sales_genio.business.campaign.position', ['id' => $business->id, 'pkg' => $package->id])
                ->with('error', $quote['clash']);
        }

        $key = 'campaign_cart_' . $business->id;
        $cart = $request->session()->get($key, []);
        $cart[$package->id] = [
            'package_id' => $package->id,
            'name'       => $package->name,
            'plan'       => $quote['summary'] . ' · ' . $quote['tenure'] . ' · ' . $business->city,
            'price'      => $quote['total'],
            'days'       => $quote['days'],
            'lines'      => $quote['lines'],
            'city'       => $business->city,
        ];

        // A top-position campaign carries a free certificate.
        $freebie = \App\Models\CertificatePackage::where('status', 1)
            ->where('perk_type', 'certificate')->orderBy('id')->first();

        $free = null;
        if ($freebie && !isset($cart[$freebie->id])) {
            $cart[$freebie->id] = [
                'package_id' => $freebie->id,
                'name'       => $freebie->name,
                'plan'       => 'Free with ' . $package->name,
                'price'      => 0,
                'days'       => $quote['days'],
                'free'       => true,
            ];
            $free = [
                'name'  => $freebie->name,
                'note'  => collect($freebie->benefitList())->first() ?? 'Included free with this campaign.',
                'image' => $freebie->imageUrl(),
                'days'  => $quote['days'],
            ];
        }

        $request->session()->put($key, $cart);
        $request->session()->forget(self::pickKey($business->id, $package->id));

        return redirect()->route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id])
            ->with('success', $package->name . ' — ' . $quote['summary'] . ' added to the pitch list.')
            ->with('free_addon', $free);
    }

    /**
     * Prices the positions the rep picked: every line, the tenure share and the
     * total — always recomputed here, never carried from the page.
     */
    private function buildQuote(Request $request, \App\Models\Listing $business, $package): ?array
    {
        $pick = $request->session()->get(self::pickKey($business->id, $package->id));

        if (!$pick || empty($pick['positions'])) {
            return null;
        }

        $fullDays = (int) ($package->validity_days ?: 365);
        $tenures = self::tenureOptions($fullDays);
        $days = (int) $pick['days'];

        if (!isset($tenures[$days])) {
            return null;
        }

        $pincodes = $request->session()->get(self::areaKey($business->id, $package->id), []);
        $groups = collect(self::cityAreaGroups($business->city))->whereIn('pincode', $pincodes)->keyBy('pincode');
        $rates = self::tierRates($package, $business->city);
        $taken = self::takenSlots($business, $groups->keys()->all());

        $lines = [];
        $total = 0;
        $clash = null;

        foreach ($pick['positions'] as $pincode => $tier) {
            if (!$groups->has($pincode) || !isset($rates[$tier])) {
                continue;
            }

            if (isset($taken[$pincode][$tier])) {
                $clash = $tier . ' in ' . $groups[$pincode]['label'] . ' has just been taken — pick another position there.';
                continue;
            }

            // Rounded per area, so the lines always add up to the total.
            $amount = round($rates[$tier] * $days / max(1, $fullDays));
            $total += $amount;

            $lines[] = [
                'area'    => $groups[$pincode]['label'],
                'pincode' => $pincode,
                'tier'    => $tier,
                'amount'  => $amount,
            ];
        }

        if (!$lines) {
            return null;
        }

        return [
            'lines'   => $lines,
            'days'    => $days,
            'tenure'  => $tenures[$days],
            'total'   => round($total),
            'summary' => collect($lines)->countBy('tier')->map(fn ($n, $tier) => $tier . ' × ' . $n)->implode(', '),
            'clash'   => $clash,
        ];
    }

    private static function pickKey(int $listingId, int $packageId): string
    {
        return 'campaign_pick_' . $listingId . '_' . $packageId;
    }


    /**
     * Positions already held by someone else in these areas, for this
     * category: [pincode => [tier => ['business' => .., 'till' => ..]]].
     */
    public static function takenSlots(\App\Models\Listing $business, array $pincodes): array
    {
        $taken = \App\Models\ListingPositionSlot::live()
            ->where('city', $business->city)
            ->where('category_id', $business->category_id)
            ->whereIn('pincode', $pincodes)
            ->where('listing_id', '!=', $business->id)
            ->with('listing:id,name')
            ->get()
            ->groupBy('pincode')
            ->map(fn ($rows) => $rows->keyBy('tier')->map(fn ($row) => [
                'business' => optional($row->listing)->name ?? 'another business',
                'till'     => optional($row->expires_at)->format('d M Y'),
            ])->all())
            ->all();

        // A slot sitting on an unpaid payment link is held, not free — two reps
        // must not sell the same position while one customer is paying.
        $held = \App\Models\SalesOrderItem::whereIn('sales_order_id',
                \App\Models\SalesOrder::where('status', \App\Models\SalesOrder::PENDING)
                    ->where('listing_id', '!=', $business->id)
                    ->where('created_at', '>', now()->subDays(7))
                    ->pluck('id'))
            ->get();

        foreach ($held as $item) {
            foreach ($item->meta['lines'] ?? [] as $line) {
                $pincode = (string) ($line['pincode'] ?? '');

                if (!in_array($pincode, $pincodes, true) || isset($taken[$pincode][$line['tier']])) {
                    continue;
                }

                $taken[$pincode][$line['tier']] = [
                    'business' => 'another business',
                    'till'     => 'payment pending',
                ];
            }
        }

        return $taken;
    }

    /**
     * The rate for ONE area at each position, for the package's full tenure.
     * Every selected area is charged this same rate, so the campaign total is
     * simply rate x number of areas.
     */
    public static function tierRates($package, ?string $city = null): array
    {
        // One rate per position, charged the same in every area and every city.
        // The package's own price list is the source; the city/area screen is
        // only a fallback for packages priced there before.
        $planPrices = collect($package->planList())->pluck('price', 'label');
        $cityPrices = $package->positionPriceTree();

        $rates = [];
        foreach ($package->tierLabels() as $tier) {
            $rate = $planPrices[$tier] ?? null;

            if ($rate === null) {
                $rate = collect($cityPrices)
                    ->flatMap(fn ($areas) => collect($areas)->map(fn ($prices) => $prices[$tier] ?? null))
                    ->filter()->min();
            }

            $rates[$tier] = $rate !== null ? (float) $rate : null;
        }

        return array_filter($rates, fn ($r) => $r !== null);
    }

    /** The category the campaign runs under — the business's own, nothing else. */
    public static function campaignCategories(\App\Models\Listing $business): array
    {
        if (!$business->category) {
            return [];
        }

        return [[
            'name'  => $business->category->name,
            'share' => 1.0,
            'main'  => true,
        ]];
    }

    /** Shorter tenures the rep can sell, priced pro-rata from the full term. */
    public static function tenureOptions(int $fullDays): array
    {
        $options = [];

        foreach ([30 => '1 Month', 90 => '90 Days', 180 => '180 Days'] as $days => $label) {
            if ($days < $fullDays) {
                $options[$days] = $label;
            }
        }

        $options[$fullDays] = $fullDays % 365 === 0
            ? ($fullDays / 365) . ' Year' . ($fullDays > 365 ? 's' : '')
            : $fullDays . ' Days';

        return $options;
    }

    private static function areaKey(int $listingId, int $packageId): string
    {
        return 'campaign_areas_' . $listingId . '_' . $packageId;
    }

    /** The pincode group the business itself sits in. */
    private static function ownPincode(\App\Models\Listing $business, array $groups): ?string
    {
        foreach ($groups as $group) {
            if ($group['pincode'] === $business->pincode) {
                return $group['pincode'];
            }
        }

        return $groups[0]['pincode'] ?? null;
    }

    /**
     * A city's areas grouped by pincode, e.g. "Mansarovar-302020" with the
     * localities inside it — taken from the listings already in that city.
     */
    public static function cityAreaGroups(?string $city): array
    {
        if (!$city) {
            return [];
        }

        $rows = \App\Models\Listing::query()
            ->where('city', $city)
            ->whereNotNull('pincode')->where('pincode', '!=', '')
            ->whereNotNull('area')->where('area', '!=', '')
            ->selectRaw('pincode, area, count(*) as c')
            ->groupBy('pincode', 'area')
            ->orderByDesc('c')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $groups[$row->pincode]['pincode'] = $row->pincode;
            $groups[$row->pincode]['areas'][] = $row->area;
            $groups[$row->pincode]['count'] = ($groups[$row->pincode]['count'] ?? 0) + (int) $row->c;
        }

        return collect($groups)
            ->map(function ($group) {
                // The busiest area name is what the pincode is known by.
                $group['label'] = $group['areas'][0];
                $group['others'] = array_slice($group['areas'], 1);
                return $group;
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * Drops anything from the carts that the business has already paid for —
     * the payment happens on the customer's browser, so the rep's cart is
     * tidied the next time they look at it.
     */
    public static function prunePaidCarts(Request $request): int
    {
        $removed = 0;

        foreach ($request->session()->all() as $key => $items) {
            if (!str_starts_with($key, 'campaign_cart_') || !is_array($items) || !$items) {
                continue;
            }

            $listingId = (int) str_replace('campaign_cart_', '', $key);

            $active = \App\Models\ListingSalesPackage::where('listing_id', $listingId)
                ->where('expires_at', '>', now())
                ->pluck('certificate_package_id')->all();

            if (!$active) {
                continue;
            }

            $kept = array_filter($items, fn ($item) => !in_array($item['package_id'], $active, true));

            if (count($kept) !== count($items)) {
                $removed += count($items) - count($kept);
                $kept ? $request->session()->put($key, $kept) : $request->session()->forget($key);
            }
        }

        return $removed;
    }

    /** Every pitch list the rep is carrying, across businesses. */
    public function carts(Request $request)
    {
        $user = Auth::guard('sales')->user();
        $pruned = self::prunePaidCarts($request);

        $carts = collect($request->session()->all())
            ->filter(fn ($value, $key) => str_starts_with($key, 'campaign_cart_') && is_array($value) && $value)
            ->map(function ($items, $key) {
                $listingId = (int) str_replace('campaign_cart_', '', $key);
                $listing = \App\Models\Listing::find($listingId);

                if (!$listing) {
                    return null;
                }

                return [
                    'listing' => $listing,
                    'count'   => count($items),
                    'total'   => collect($items)->sum('price'),
                    'names'   => collect($items)->pluck('name')->implode(', '),
                ];
            })
            ->filter()
            ->values();

        // One cart open? Go straight there — no need for a chooser.
        if ($carts->count() === 1) {
            return redirect()->route('sales_genio.business.cart', $carts->first()['listing']->id);
        }

        $orders = \App\Models\SalesOrder::where('sales_user_id', $user->id)
            ->where('status', \App\Models\SalesOrder::PENDING)
            ->with('listing:id,name,city')->latest()->limit(10)->get();

        return view('sales_genio.carts', compact('user', 'carts', 'orders'));
    }

    /** The rep's cart for one business: what is being sold on this visit. */
    public function cart(Request $request, $id)
    {
        $user = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $packages = \App\Models\CertificatePackage::where('status', 1)->orderBy('id')->get();

        $pruned = self::prunePaidCarts($request);
        $cart = collect($request->session()->get('campaign_cart_' . $business->id, []));

        $active = \App\Models\ListingSalesPackage::where('listing_id', $business->id)
            ->where('expires_at', '>', now())->pluck('expires_at', 'certificate_package_id');

        $items = $cart->map(function ($item) use ($packages, $business, $active) {
            $package = $packages->firstWhere('id', $item['package_id']);
            $variant = $package ? \App\Support\SalesPreview::variantFor($package) : null;

            $item['image'] = $variant
                ? (\App\Support\SalesPreview::url($business, $variant) ?: optional($package)->imageUrl())
                : optional($package)->imageUrl();
            $item['position_priced'] = $package && $package->isPositionPriced();
            $item['active_till'] = $active[$item['package_id']] ?? null;

            return $item;
        })->values();

        // Links already sent for this business that nobody has paid yet.
        $pendingOrders = \App\Models\SalesOrder::where('listing_id', $business->id)
            ->where('status', \App\Models\SalesOrder::PENDING)
            ->withCount('items')->latest()->get();

        // Everything not in the cart yet, cheapest plan shown as the "from" price.
        $suggestions = $packages->reject(fn ($p) => $cart->has($p->id))
            ->map(function ($package) use ($business) {
                $variant = \App\Support\SalesPreview::variantFor($package);

                return [
                    'id'    => $package->id,
                    'name'  => $package->name,
                    'image' => $variant
                        ? (\App\Support\SalesPreview::url($business, $variant) ?: $package->imageUrl())
                        : $package->imageUrl(),
                    'from'  => $package->isPositionPriced()
                        ? collect(self::tierRates($package, $business->city))->min()
                        : collect($package->planList())->min('price'),
                    'flow'  => $package->isPositionPriced(),
                ];
            })->values();

        return view('sales_genio.cart', compact(
            'user', 'business', 'packages', 'items', 'suggestions', 'pendingOrders', 'pruned'
        ));
    }

    /**
     * Collect payment for the cart: the gateways this site actually has
     * (Razorpay and Cashfree), GST, and the link the owner pays through.
     */
    public function cartCheckout(Request $request, $id)
    {
        $user = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $packages = \App\Models\CertificatePackage::where('status', 1)->orderBy('id')->get();

        $items = collect($request->session()->get('campaign_cart_' . $business->id, []))->values();

        if ($items->where('price', '>', 0)->isEmpty()) {
            return redirect()->route('sales_genio.business.cart', $business->id)
                ->with('error', 'Add a paid package before collecting payment.');
        }

        $methods = \App\Models\MethodCurrency::where('enable_status', 1)
            ->whereHas('paymentMethod', fn ($q) => $q->whereIn('act', ['razorpay', 'cashfree']))
            ->with('paymentMethod')->get();

        $subtotal = $items->sum('price');
        $gst = round($subtotal * 0.18);

        // The owner pays from their own business page.
        $payLink = route('user.listing.advertise', $business->id);

        return view('sales_genio.cart_checkout', compact(
            'user', 'business', 'packages', 'items', 'methods', 'subtotal', 'gst', 'payLink'
        ));
    }

    /** Turns the cart into a payment link the customer can open and pay. */
    public function generateOrder(Request $request, $id)
    {
        $user = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);

        $items = collect($request->session()->get('campaign_cart_' . $business->id, []))->values();

        if ($items->where('price', '>', 0)->isEmpty()) {
            return back()->with('error', 'Add a paid package before making a payment link.');
        }

        $subtotal = $items->sum('price');
        $gst = round($subtotal * 0.18);

        $order = \App\Models\SalesOrder::create([
            'token'         => \App\Models\SalesOrder::newToken(),
            'listing_id'    => $business->id,
            'sales_user_id' => $user->id,
            'subtotal'      => $subtotal,
            'gst'           => $gst,
            'total'         => $subtotal + $gst,
            'status'        => \App\Models\SalesOrder::PENDING,
        ]);

        foreach ($items as $item) {
            $pick = $request->session()->get(self::pickKey($business->id, $item['package_id']));

            $order->items()->create([
                'certificate_package_id' => $item['package_id'],
                'name'                   => $item['name'],
                'plan'                   => $item['plan'],
                'price'                  => $item['price'],
                'days'                   => $item['days'],
                'is_free'                => !empty($item['free']),
                'meta'                   => [
                    'city'      => $item['city'] ?? $business->city,
                    'area'      => $business->area,
                    'lines'     => $item['lines'] ?? [],
                    'positions' => collect($item['lines'] ?? [])->pluck('tier', 'pincode')->all()
                        ?: ($pick['positions'] ?? []),
                ],
            ]);
        }

        // The cart stays until the order is actually paid — an unpaid link
        // must never lose the rep's work.
        return redirect()->route('sales_genio.business.order', ['id' => $business->id, 'order' => $order->id]);
    }

    /** Sends the approved payment-link template through Gupshup. */
    public function sendOrderWhatsapp(Request $request, $id, $order)
    {
        $business = \App\Models\Listing::findOrFail($id);
        $order = \App\Models\SalesOrder::where('listing_id', $business->id)->with('items')->findOrFail($order);

        $sender = app(\App\Services\WhatsappSender::class);
        $templateId = $sender->paymentTemplateId();

        if (!$templateId) {
            return back()->with('error', 'No payment template is set in Settings → WhatsApp.');
        }

        $phone = \App\Services\WhatsappSender::listingPhone($business);

        if (!$phone) {
            return back()->with('error', 'This business has no usable WhatsApp number.');
        }

        // Body {{1}}–{{4}}, then the dynamic button's suffix: the order token.
        $params = [
            $business->contact_person ?: $business->name,
            $business->name,
            numberFormat($order->total),
            (string) $order->id,
            $order->token,
        ];

        $message = $sender->sendTemplate($phone, $templateId, $params, [
            'listing_id'     => $business->id,
            'contact_name'   => $business->name,
            'sales_order_id' => $order->id,
        ]);

        $failed = isset($message->status) && strtolower((string) $message->status) === 'failed';

        return back()->with(
            $failed ? 'error' : 'success',
            $failed
                ? 'WhatsApp could not send it: ' . ($message->error ?? 'unknown error')
                : 'Payment link sent to ' . $phone . ' on WhatsApp.'
        );
    }

    /** The finished link, ready to share. */
    public function showOrder(Request $request, $id, $order)
    {
        $user = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $packages = \App\Models\CertificatePackage::where('status', 1)->orderBy('id')->get();

        $order = \App\Models\SalesOrder::where('listing_id', $business->id)->with('items')->findOrFail($order);

        return view('sales_genio.order_link', compact('user', 'business', 'packages', 'order'));
    }

    /** Quick-add from the cart's suggestions: the package's cheapest plan. */
    public function quickAddCart(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);
        $package = \App\Models\CertificatePackage::where('status', 1)->findOrFail($request->package_id);

        // Anything priced by position goes through its own area flow.
        if ($package->isPositionPriced()) {
            return redirect()->route('sales_genio.business.campaign.areas', ['id' => $business->id, 'pkg' => $package->id]);
        }

        $plans = $package->planList();

        if (!$plans) {
            return back()->with('error', $package->name . ' has no plan to add yet.');
        }

        $cheapest = collect($plans)->sortBy('price')->keys()->first();
        $choice = $package->resolveChoice([
            'plan' => $cheapest,
            'tier' => $plans[$cheapest]['label'] ?? null,
            'city' => $business->city,
            'area' => '',
        ]);

        if (!$choice) {
            return back()->with('error', 'Could not price ' . $package->name . ' for this city.');
        }

        $key = 'campaign_cart_' . $business->id;
        $cart = $request->session()->get($key, []);
        $cart[$package->id] = [
            'package_id' => $package->id,
            'name'       => $package->name,
            'plan'       => $choice['label'],
            'price'      => $choice['price'],
            'days'       => $choice['days'],
        ];
        $request->session()->put($key, $cart);

        return back()->with('success', $package->name . ' added — change the plan any time.');
    }

    public function clearCart(Request $request, $id)
    {
        $request->session()->forget('campaign_cart_' . $id);

        return back()->with('success', 'Cart cleared.');
    }

    /** The rep's pitch list for this business, kept for the visit. */
    public function addCampaignCart(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'package_id' => 'required|integer',
            'plan'       => 'nullable|string|max:60',
            'tier'       => 'nullable|string|max:60',
            'city'       => 'nullable|string|max:100',
            'area'       => 'nullable|string|max:150',
        ]);

        $package = \App\Models\CertificatePackage::where('status', 1)->findOrFail($request->package_id);

        $choice = $package->resolveChoice([
            'plan' => $request->input('plan'),
            'tier' => $request->input('tier'),
            'city' => $request->input('city', $business->city),
            'area' => $request->input('area', ''),
        ]);

        if (!$choice) {
            return back()->with('error', 'Please pick a plan first.');
        }

        $key = 'campaign_cart_' . $business->id;
        $cart = $request->session()->get($key, []);
        $cart[$package->id] = [
            'package_id' => $package->id,
            'name'       => $package->name,
            'plan'       => $choice['label'],
            'price'      => $choice['price'],
            'days'       => $choice['days'],
        ];
        $request->session()->put($key, $cart);

        return redirect()->route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $package->id])
            ->with('success', $package->name . ' — ' . $choice['label'] . ' added to the pitch list.');
    }

    public function removeCampaignCart(Request $request, $id)
    {
        $key = 'campaign_cart_' . $id;
        $cart = $request->session()->get($key, []);
        unset($cart[(int) $request->input('package_id')]);
        $request->session()->put($key, $cart);

        return back();
    }

    /** Remembers that the rep chose to skip the optional details for now. */
    public function skipCampaignOptional(Request $request, $id)
    {
        $request->session()->put('campaign_optional_skipped_' . $id, true);

        return redirect()->route('sales_genio.business.campaign', $id);
    }

    /**
     * [$required, $optional] — each item: label, whether it is done, and where
     * to go and fill it.
     */
    public static function campaignChecklist(\App\Models\Listing $b): array
    {
        $hasTimings = collect((array) $b->open_time)->filter()->isNotEmpty();
        $photoCount = count((array) $b->photos) + $b->extraImages()->count();
        $kyc = $b->kycDocuments()->get(['doc_type', 'status']);
        $isAddress = fn ($type) => str_contains($type, 'aadhaar') || str_contains($type, 'address');

        $kycState = function (callable $match) use ($kyc) {
            $rows = $kyc->filter(fn ($d) => $match(strtolower((string) $d->doc_type)));

            if ($rows->contains(fn ($d) => strtolower((string) $d->status) === 'approved')) {
                return 'approved';
            }

            return $rows->isNotEmpty() ? 'in_review' : 'missing';
        };

        $panState = $kycState(fn ($type) => str_contains($type, 'pan'));
        $addressState = $kycState($isAddress);

        $required = [
            ['label' => 'Business name',      'done' => filled($b->name),        'route' => 'sales_genio.business.name'],
            ['label' => 'Primary mobile',     'done' => filled($b->mobile),      'route' => 'sales_genio.business.contact'],
            ['label' => 'Address & pincode',  'done' => filled($b->area) && filled($b->pincode) && filled($b->city), 'route' => 'sales_genio.business.address'],
            ['label' => 'Category',           'done' => filled($b->category_id), 'route' => 'sales_genio.business.categories'],
            ['label' => 'Business timings',   'done' => $hasTimings,             'route' => 'sales_genio.business.timings'],
            ['label' => 'At least one photo', 'done' => $photoCount > 0,         'route' => 'sales_genio.business.photos'],
            ['label' => 'KYC — PAN', 'done' => $panState === 'approved', 'route' => 'sales_genio.business.kyc',
             'state' => $panState],
            ['label' => 'KYC — Address proof', 'done' => $addressState === 'approved', 'route' => 'sales_genio.business.kyc',
             'state' => $addressState],
        ];

        $optional = [
            ['label' => 'Backup mobile number', 'why' => 'So a customer is never lost when one number is unreachable.',
             'done' => count(array_filter((array) $b->mobile_numbers)) > 1, 'route' => 'sales_genio.business.contact'],
            ['label' => 'Email address', 'why' => 'To receive buyer leads and account alerts.',
             'done' => filled($b->email), 'route' => 'sales_genio.business.contact'],
            ['label' => 'WhatsApp number', 'why' => 'Customers can message the business directly.',
             'done' => count(array_filter((array) $b->whatsapp_numbers)) > 0, 'route' => 'sales_genio.business.contact'],
            ['label' => 'Website', 'why' => 'Sends visitors to their own site.',
             'done' => filled($b->website), 'route' => 'sales_genio.business.website'],
            ['label' => 'Social media links', 'why' => 'Builds trust with buyers who check socials.',
             'done' => count(array_filter((array) $b->social_links)) > 0, 'route' => 'sales_genio.business.social'],
            ['label' => 'Year of establishment', 'why' => 'Older businesses convert better.',
             'done' => filled($b->established_year), 'route' => 'sales_genio.business.establishment'],
            ['label' => 'Preferred language', 'why' => 'So the team calls in the language they prefer.',
             'done' => filled($b->preferred_language), 'route' => 'sales_genio.business.language'],
        ];

        return [$required, $optional];
    }

    /** The city picked in the top bar, if any. */
    public static function activeCity(): ?string
    {
        $city = trim((string) session('sales_city'));

        return $city !== '' ? $city : null;
    }

    /** The city the rep is working in, kept for the session. */
    /** Back to every city. */
    public function clearCity(Request $request)
    {
        $request->session()->forget('sales_city');

        return back();
    }

    public function switchCity(Request $request)
    {
        $request->validate(['city' => 'required|string|max:100']);

        $request->session()->put('sales_city', trim($request->city));

        return back();
    }

    public function createBusiness()
    {
        $user       = Auth::guard('sales')->user();
        $categories = \App\Models\Category::where('status', 1)->orderBy('name')->get(['id', 'name']);
        return view('sales_genio.create_business', compact('user', 'categories'));
    }

    private function businessRules(): array
    {
        return [
            'business_name'  => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'mobile'         => 'required|digits:10',
            'mobile_confirm' => 'required|same:mobile',
            'landline'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'email_confirm'  => 'nullable|same:email',
            'website'        => 'nullable|string|max:255',
            'area'           => 'required|string|max:255',
            'pincode'        => 'required|digits:6',
            'city'           => 'nullable|string|max:100',
            'category_id'    => 'nullable|exists:categories,id',
        ];
    }

    private function businessMessages(): array
    {
        return [
            'mobile_confirm.same' => 'Mobile numbers do not match.',
            'email_confirm.same'  => 'Email addresses do not match.',
        ];
    }

    /**
     * Proceed step — check if a listing with this mobile already exists.
     * Shows suggested matches (like JustDial) so the sales user can decide
     * to edit an existing one or create a fresh business.
     */
    public function checkBusiness(Request $request)
    {
        $request->validate($this->businessRules(), $this->businessMessages());

        $mobile = $request->mobile;

        $matches = \App\Models\Listing::query()
            ->where(function ($q) use ($mobile) {
                $q->where('mobile', $mobile)
                  ->orWhere('mobile_numbers', 'like', '%' . $mobile . '%')
                  ->orWhere('whatsapp_numbers', 'like', '%' . $mobile . '%');
            })
            ->select('id', 'name', 'slug', 'area', 'pincode', 'city', 'mobile')
            ->limit(25)
            ->get();

        $formData = $request->only([
            'business_name', 'contact_person', 'mobile', 'mobile_confirm',
            'landline', 'email', 'email_confirm', 'website', 'area', 'pincode', 'city', 'category_id',
        ]);

        // No existing match — create straight away (auto-approved).
        if ($matches->isEmpty()) {
            return $this->persistBusiness($request);
        }

        return view('sales_genio.suggested_matches', [
            'user'        => Auth::guard('sales')->user(),
            'matches'     => $matches,
            'freeMatches' => collect(),
            'formData'    => $formData,
            'mobile'      => $mobile,
        ]);
    }

    public function storeBusiness(Request $request)
    {
        $request->validate($this->businessRules(), $this->businessMessages());
        return $this->persistBusiness($request);
    }

    private function persistBusiness(Request $request)
    {
        $user = Auth::guard('sales')->user();
        $city = $request->filled('city') ? $request->city : $user->city;

        // Map the city name to a location row so the listing behaves like a
        // normal one (city pages, filters). Null if the city isn't a location.
        $locationId = \App\Models\Location::where('name', $city)->value('id');

        $listing = new \App\Models\Listing();
        $listing->name             = $request->business_name;
        $listing->contact_person   = $request->contact_person;
        $listing->mobile           = $request->mobile;
        $listing->mobile_numbers   = [$request->mobile];
        $listing->landline_numbers = $request->filled('landline') ? [$request->landline] : [];
        $listing->email            = $request->email;
        $listing->website          = $this->cleanWebsite($request->website);
        $listing->area             = $request->area;
        $listing->pincode          = $request->pincode;
        $listing->city             = $city;
        $listing->state            = $this->lookupState($request->pincode, $city);
        $listing->location_id      = $locationId;
        $listing->category_id      = $request->category_id ?: null;
        // With a category the listing goes live immediately; without one its
        // public URL can't be built, so it stays INACTIVE (status 3, not the
        // pending queue) until a category is added — then it auto-activates.
        $listing->status           = $request->filled('category_id') ? 1 : 3;
        $listing->source           = 'sales_genio';
        $listing->sales_user_id    = $user->id;
        $listing->save();

        // Clean slug like the rest of the site (only a -N suffix on collision).
        $listing->slug = $this->uniqueSlug($request->business_name ?: 'listing', $listing->id);
        $listing->save();

        $msg = $listing->status == 1
            ? 'Business created & approved successfully.'
            : 'Business saved. Add a Business Category to make it live.';

        return redirect()->route('sales_genio.business.profile', $listing->id)
            ->with('success', $msg);
    }

    /**
     * Build a uniform photo collection from a listing's `photos` JSON array
     * (filenames stored under assets/images/listing/photos), so the gallery
     * and profile views can render them the same way regardless of source.
     */
    private function listingPhotos(\App\Models\Listing $listing)
    {
        // The business image (listings.thumb) is the one the search results and
        // the listing card show, so it belongs at the front of the gallery.
        $cover = collect();
        if (!empty($listing->thumb) && is_file(public_path('assets/images/listing/thumb/' . $listing->thumb))) {
            $cover->push((object) [
                'id'   => $listing->thumb,
                'path' => 'assets/images/listing/thumb/' . $listing->thumb,
                'type' => 'cover',
            ]);
        }

        $photos = collect($listing->photos ?? [])->map(fn ($file) => (object) [
            'id'   => $file,
            'path' => 'assets/images/listing/photos/' . $file,
            'type' => 'photo',
        ]);
        $videos = collect($listing->videos ?? [])->map(function ($file) {
            $poster = self::VIDEO_POSTER_DIR . '/' . pathinfo($file, PATHINFO_FILENAME) . '.webp';

            return (object) [
                'id'     => $file,
                'path'   => 'assets/images/listing/videos/' . $file,
                'type'   => 'video',
                // Still frame grabbed by the browser the first time the video
                // was shown; null until then, and the gallery makes one.
                'poster' => is_file(public_path($poster)) ? $poster : null,
            ];
        });
        return $cover->concat($photos)->concat($videos)->values();
    }

    /** Everything the gallery counts: cover + photos + videos. */
    private function mediaCount(\App\Models\Listing $listing): int
    {
        return (empty($listing->thumb) ? 0 : 1)
            + count($listing->photos ?? [])
            + count($listing->videos ?? []);
    }

    public function businessProfile($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $photos   = $this->listingPhotos($business);
        $activeDispositions = \App\Models\ListingDisposition::where('listing_id', $id)->pluck('disposition')->all();

        // What still blocks a campaign — shown as a popup on Manage Campaign.
        [$required, $optional] = self::campaignChecklist($business);
        $campaignMissing = collect($required)->where('done', false)->values()->all();
        $campaignOptional = collect($optional)->where('done', false)->values()->all();
        $optionalSkipped = (bool) session('campaign_optional_skipped_' . $business->id);

        return view('sales_genio.business_profile', compact(
            'user', 'business', 'photos', 'activeDispositions',
            'campaignMissing', 'campaignOptional', 'optionalSkipped'
        ));
    }

    public function editAddress($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);

        // Backfill state for older rows that were saved without one.
        if (empty($business->state)) {
            $business->state = $this->lookupState($business->pincode, $business->city);
        }

        return view('sales_genio.address_edit', compact('user', 'business'));
    }

    /**
     * Best-effort state for a pincode: read it off an existing listing with
     * the same pincode, then fall back to the same city. Returns '' if unknown.
     */
    private function lookupState(?string $pincode, ?string $city): string
    {
        if ($pincode) {
            $state = \App\Models\Listing::whereNotNull('state')->where('state', '<>', '')
                ->where('pincode', $pincode)->value('state');
            if ($state) return $state;
        }
        if ($city) {
            $state = \App\Models\Listing::whereNotNull('state')->where('state', '<>', '')
                ->where('city', $city)->value('state');
            if ($state) return $state;
        }
        return '';
    }

    public function updateAddress(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'plot_no'       => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'street_name'   => 'nullable|string|max:255',
            'landmark'      => 'nullable|string|max:255',
        ]);

        // Area, pincode, city and state are locked after creation — only the
        // detailed address lines can be edited here.
        foreach (['plot_no', 'building_name', 'street_name', 'landmark'] as $f) {
            $business->$f = $request->input($f);
        }
        $business->save();

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Address updated successfully.');
    }

    public function editCategories($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);

        $categories = \App\Models\Category::where('status', 1)->orderBy('name')->get(['id', 'name']);

        // Keywords (subcategories) available for this listing's main category.
        $available = \App\Models\Subcategory::where('status', 1)
            ->when($business->category_id, fn ($q) => $q->where('category_id', $business->category_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedIds = collect($business->subcategory_ids ?? [])->map(fn ($v) => (int) $v)->all();

        return view('sales_genio.categories_edit', compact('user', 'business', 'categories', 'available', 'selectedIds'));
    }

    public function updateCategories(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'category_id'       => 'nullable|exists:categories,id',
            'subcategory_ids'   => 'nullable|array',
            'subcategory_ids.*' => 'integer|exists:subcategories,id',
        ]);

        $ids   = array_values(array_unique(array_map('intval', $request->subcategory_ids ?? [])));
        $names = \App\Models\Subcategory::whereIn('id', $ids)->pluck('name', 'id');

        // Keep names in the same order the ids were submitted.
        $orderedNames = collect($ids)->map(fn ($i) => $names[$i] ?? null)->filter()->values()->all();

        $business->category_id         = $request->category_id ?: null;
        $business->subcategory_ids     = array_map('strval', $ids);
        $business->business_categories = $orderedNames;

        // Auto-activate: an inactive listing (status 3, no category) becomes
        // live the moment a main category is set. If the category is cleared,
        // it drops back to inactive.
        if ($request->filled('category_id')) {
            if ((int) $business->status === 3) {
                $business->status = 1;
            }
        } elseif ((int) $business->status === 1 && $business->source === 'sales_genio') {
            $business->status = 3;
        }

        $business->save();

        $msg = ((int) $business->status === 1)
            ? 'Business categories updated. Listing is live.'
            : 'Saved. Add a main category to make this listing live.';

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', $msg);
    }

    /** KYC document types: key => [label, mandatory, group]. */
    public static function kycTypes(): array
    {
        return [
            'pan'             => ['PAN Card',         true,  'main'],
            'address'         => ['Address Proof',    true,  'main'],
            'aadhaar'         => ['Aadhaar Card',     false, 'company'],
            'gst'             => ['GST Certificate',  false, 'company'],
            'msme'            => ['MSME Certificate', false, 'company'],
            'driving_licence' => ['Driving Licence',  false, 'company'],
            'voter_id'        => ['Voter ID',         false, 'company'],
            'office_photos'   => ['Office Photos',    false, 'company'],
        ];
    }

    public function editMap($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        return view('sales_genio.map_edit', compact('user', 'business'));
    }

    public function updateMap(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $business->latitude  = $request->latitude;
        $business->longitude = $request->longitude;
        $business->save();

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Map location updated successfully.');
    }

    /** Labels for the award/certificate editor by type. */
    private function awardMeta(string $type): array
    {
        return $type === 'certificate'
            ? ['title' => 'Certificate Details', 'block' => 'Certificate', 'name_ph' => 'Certificate Title', 'by_ph' => 'Certified By', 'add' => 'Add Another Certificate', 'upload' => 'Upload Certificate Photo']
            : ['title' => 'Awards & Recognition', 'block' => 'Award & Recognition', 'name_ph' => 'Award Name', 'by_ph' => 'Awarded By', 'add' => 'Add Another Award', 'upload' => 'Upload Award Photo'];
    }

    public function editAwards($id) { return $this->editAwardItems($id, 'award'); }
    public function updateAwards(Request $request, $id) { return $this->updateAwardItems($request, $id, 'award'); }
    public function editCertificates($id) { return $this->editAwardItems($id, 'certificate'); }
    public function updateCertificates(Request $request, $id) { return $this->updateAwardItems($request, $id, 'certificate'); }

    private function editAwardItems($id, string $type)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $awards   = \App\Models\ListingAward::where('listing_id', $id)->where('type', $type)->get();
        $meta     = $this->awardMeta($type);
        return view('sales_genio.awards_edit', compact('user', 'business', 'awards', 'type', 'meta'));
    }

    private function updateAwardItems(Request $request, $id, string $type)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'awards'              => 'nullable|array',
            'awards.*.name'       => 'nullable|string|max:255',
            'awards.*.awarded_by' => 'nullable|string|max:255',
            'awards.*.month'      => 'nullable|integer|between:1,12',
            'awards.*.year'       => 'nullable|digits:4',
            'award_photo.*'       => 'nullable|image|max:20480',
        ]);

        $dir = 'assets/images/sales_genio/awards/' . $business->id;
        $fullDir = public_path($dir);
        if (!is_dir($fullDir)) mkdir($fullDir, 0755, true);

        $existing = \App\Models\ListingAward::where('listing_id', $business->id)->where('type', $type)->get();
        $keepPhotos = [];

        \App\Models\ListingAward::where('listing_id', $business->id)->where('type', $type)->delete();

        foreach ($request->input('awards', []) as $i => $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') continue;

            $photo = $row['existing_photo'] ?? null;
            if ($request->hasFile("award_photo.$i")) {
                $filename = $type . '_' . uniqid() . '_' . time() . '.webp';
                $this->resizeToWebp($request->file("award_photo.$i")->getRealPath(), $fullDir . '/' . $filename, 1200);
                $photo = $dir . '/' . $filename;
            }
            if ($photo) $keepPhotos[] = $photo;

            \App\Models\ListingAward::create([
                'listing_id' => $business->id,
                'type'       => $type,
                'name'       => $name,
                'awarded_by' => $row['awarded_by'] ?? null,
                'month'      => $row['month'] ?? null,
                'year'       => $row['year'] ?? null,
                'photo'      => $photo,
            ]);
        }

        foreach ($existing as $old) {
            if ($old->photo && !in_array($old->photo, $keepPhotos, true)) {
                $f = public_path($old->photo);
                if (is_file($f)) @unlink($f);
            }
        }

        $label = $type === 'certificate' ? 'Certificates' : 'Awards';
        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', $label . ' updated successfully.');
    }

    public static function dispositionOptions(): array
    {
        return ['Call Back','Follow Up','Appointment Refixed','Self Appointment','Not Available',
                'Do Not Disturb','Not Interested','Paid Client','Not Visited','Fake Appt',
                'Bounced Collection','Details Incorrect','Company Closed'];
    }

    public function saveDisposition(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);
        $user = Auth::guard('sales')->user();

        $request->validate([
            'disposition' => 'required|string|in:' . implode(',', self::dispositionOptions()),
            'value'       => 'nullable|boolean',
            'call_back'   => 'nullable|boolean',
            'note'        => 'nullable|string|max:500',
            'due_date'    => 'nullable|date',
            'due_time'    => 'nullable|date_format:H:i',
        ]);

        // "No" removes this disposition for the business; "Yes" upserts a single
        // row per business+disposition so it never duplicates on repeat taps.
        if (!$request->boolean('value')) {
            \App\Models\ListingDisposition::where('listing_id', $business->id)
                ->where('disposition', $request->disposition)->delete();
            return response()->json(['status' => true, 'message' => $request->disposition . ' removed']);
        }

        // Follow ups, call backs and appointments carry a date and time so the
        // rep's day can be built from them.
        $dueAt = null;
        if ($request->filled('due_date')) {
            $dueAt = \Carbon\Carbon::parse($request->due_date . ' ' . ($request->due_time ?: '10:00'));
        }

        \App\Models\ListingDisposition::updateOrCreate(
            ['listing_id' => $business->id, 'disposition' => $request->disposition],
            [
                'sales_user_id' => $user->id,
                'call_back'     => $request->boolean('call_back'),
                'due_at'        => $dueAt,
                'note'          => $request->note,
                'updated_at'    => now(),
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => $dueAt
                ? $request->disposition . ' set for ' . $dueAt->format('d M, h:i A')
                : 'Disposition saved: ' . $request->disposition,
        ]);
    }

    public static function languages(): array
    {
        return ['English','हिंदी (Hindi)','বাংলা (Bengali)','ગુજરાતી (Gujarati)','ಕನ್ನಡ (Kannada)','മലയാളം (Malayalam)','मराठी (Marathi)','ਪੰਜਾਬੀ (Punjabi)','தமிழ் (Tamil)','తెలుగు (Telugu)'];
    }

    public function editLanguage($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $languages = self::languages();
        return view('sales_genio.language_edit', compact('user', 'business', 'languages'));
    }

    public function updateLanguage(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);
        $request->validate(['preferred_language' => 'required|string|max:40']);
        $business->preferred_language = $request->preferred_language;
        $business->save();
        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Preferred language updated successfully.');
    }

    public function editName($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        return view('sales_genio.name_edit', compact('user', 'business'));
    }

    public function updateName(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $business->name = $request->name;
        // Keep the slug in step with the name (clean, unique).
        $business->slug = $this->uniqueSlug($request->name, $business->id);
        $business->save();

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Business name updated successfully.');
    }

    public function editKyc($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $docs     = \App\Models\ListingKycDocument::where('listing_id', $id)->get()->groupBy('doc_type');
        $types    = self::kycTypes();
        return view('sales_genio.kyc_edit', compact('user', 'business', 'docs', 'types'));
    }

    /**
     * Saves the number written on a KYC document. Worth asking for separately
     * from the scan: the PAN and GST numbers are printed on the customer's
     * invoice, and a scan alone cannot be read back.
     */
    public function saveKycNumber(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'doc_type'   => 'required|string|in:' . implode(',', array_keys(self::kycTypes())),
            'doc_number' => 'nullable|string|max:50',
        ]);

        $type   = $request->doc_type;
        $number = strtoupper(trim((string) $request->doc_number)) ?: null;

        // A wrong PAN or GSTIN is worse than none — it ends up on an invoice.
        $patterns = [
            'pan'     => ['/^[A-Z]{5}[0-9]{4}[A-Z]$/', 'A PAN is 10 characters, e.g. AAAAA0000A.'],
            'gst'     => ['/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', 'A GSTIN is 15 characters, e.g. 08AAAAA0000A1Z5.'],
            'aadhaar' => ['/^[0-9]{12}$/', 'An Aadhaar number is 12 digits.'],
        ];

        if ($number && isset($patterns[$type]) && !preg_match($patterns[$type][0], $number)) {
            return response()->json(['status' => false, 'message' => $patterns[$type][1]], 422);
        }

        $rows = \App\Models\ListingKycDocument::where('listing_id', $business->id)
            ->where('doc_type', $type)->get();

        if ($rows->isEmpty()) {
            // The number can be captured before the scan is uploaded.
            \App\Models\ListingKycDocument::create([
                'listing_id' => $business->id,
                'doc_type'   => $type,
                'doc_number' => $number,
                'path'       => null,
                'status'     => 'uploaded',
            ]);
        } else {
            foreach ($rows as $row) {
                $row->update(['doc_number' => $number]);
            }
        }

        return response()->json(['status' => true, 'number' => $number]);
    }

    public function uploadKyc(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'doc_type'   => 'required|string|in:' . implode(',', array_keys(self::kycTypes())),
            'doc_number' => 'nullable|string|max:50',
            'files'      => 'required|array|min:1',
            'files.*'    => 'file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $dir = 'assets/images/sales_genio/kyc/' . $business->id;
        $fullDir = public_path($dir);
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        // Single-file doc types replace the old file; these accumulate
        // (office photos, and Aadhaar which needs front + back).
        // Address proof and Aadhaar keep both sides; office photos keep all.
        $multi = in_array($request->doc_type, ['office_photos', 'aadhaar', 'address'], true);
        if (!$multi) {
            $olds = \App\Models\ListingKycDocument::where('listing_id', $business->id)
                ->where('doc_type', $request->doc_type)->get();
            foreach ($olds as $o) {
                $f = public_path($o->path);
                if (is_file($f)) @unlink($f);
                $o->delete();
            }
        }

        $saved = [];
        foreach ($request->file('files') as $file) {
            $ext      = strtolower($file->getClientOriginalExtension());
            $filename = $request->doc_type . '_' . uniqid() . '_' . time() . '.' . $ext;

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = $request->doc_type . '_' . uniqid() . '_' . time() . '.webp';
                $this->resizeToWebp($file->getRealPath(), $fullDir . '/' . $filename, 1600);
            } else {
                $file->move($fullDir, $filename);
            }

            $doc = \App\Models\ListingKycDocument::create([
                'listing_id' => $business->id,
                'doc_type'   => $request->doc_type,
                'doc_number' => $request->doc_number,
                'path'       => $dir . '/' . $filename,
                'status'     => 'in_review',
            ]);
            $saved[] = ['id' => $doc->id, 'url' => asset($doc->path)];
        }

        return response()->json(['status' => true, 'saved' => $saved]);
    }

    public function deleteKyc($id, $docId)
    {
        $doc = \App\Models\ListingKycDocument::where('listing_id', $id)->findOrFail($docId);
        $f = public_path($doc->path);
        if (is_file($f)) @unlink($f);
        $doc->delete();
        return response()->json(['status' => true]);
    }

    public function editSocial($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        return view('sales_genio.social_edit', compact('user', 'business'));
    }

    public function updateSocial(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        // Each platform's link must point at that platform's own domain.
        $domains = [
            'facebook'  => ['facebook.com', 'fb.com', 'fb.me'],
            'x'         => ['twitter.com', 'x.com'],
            'linkedin'  => ['linkedin.com', 'lnkd.in'],
            'youtube'   => ['youtube.com', 'youtu.be'],
            'instagram' => ['instagram.com', 'instagr.am'],
        ];
        $labels = [
            'facebook' => 'Facebook', 'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn',
            'youtube'  => 'YouTube', 'instagram' => 'Instagram',
        ];

        $clean = fn ($arr) => array_values(array_unique(array_filter(array_map('trim', $arr ?? []))));

        $social = [];
        $errors = [];

        foreach ($domains as $p => $allowed) {
            $links = $clean($request->input($p, []));
            $valid = [];
            foreach ($links as $link) {
                $normalized = $this->normalizeUrl($link);
                $host = strtolower(parse_url($normalized, PHP_URL_HOST) ?? '');
                $host = preg_replace('/^www\./', '', $host);

                $ok = false;
                foreach ($allowed as $d) {
                    if ($host === $d || str_ends_with($host, '.' . $d)) { $ok = true; break; }
                }

                if ($ok) {
                    $valid[] = $normalized;
                } else {
                    $errors[] = "\"{$link}\" is not a valid {$labels[$p]} link.";
                }
            }
            $social[$p] = $valid;
        }

        if (!empty($errors)) {
            return back()->withErrors(['social' => $errors])->withInput();
        }

        $business->social_links = $social;

        // The public listing page reads separate columns, so mirror the first
        // link of each platform there too ('x' maps to the twitter column).
        $business->facebook  = $social['facebook'][0]  ?? null;
        $business->twitter   = $social['x'][0]         ?? null;
        $business->linkedin  = $social['linkedin'][0]  ?? null;
        $business->youtube   = $social['youtube'][0]   ?? null;
        $business->instagram = $social['instagram'][0] ?? null;

        $business->save();

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Social media links updated successfully.');
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') return '';
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        return $url;
    }

    public function editWebsite($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        return view('sales_genio.website_edit', compact('user', 'business'));
    }

    public function updateWebsite(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'websites'   => 'nullable|array',
            'websites.*' => 'nullable|string|max:255',
        ]);

        $sites = array_values(array_unique(array_filter(array_map(
            fn ($w) => $this->cleanWebsite($w),
            $request->websites ?? []
        ))));

        $business->website        = $sites[0] ?? null;
        $business->extra_websites = array_values(array_slice($sites, 1));
        $business->save();

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Website updated successfully.');
    }

    public function editTimings($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);

        $order   = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        $days    = is_array($business->business_days) ? $business->business_days : [];
        $opens   = is_array($business->open_time) ? array_values($business->open_time) : [];
        $closes  = is_array($business->close_time) ? array_values($business->close_time) : [];

        // Map each open day to its (open, close), then group days that share the
        // same hours into one slot — that's how the editor shows them.
        $dayHours = [];
        foreach (array_values($days) as $i => $d) {
            $dayHours[$d] = [$opens[$i] ?? '10:00 AM', $closes[$i] ?? '08:00 PM'];
        }

        $slots = [];
        foreach ($dayHours as $d => $hrs) {
            $key = $hrs[0] . '|' . $hrs[1];
            $slots[$key]['days'][] = $d;
            $slots[$key]['open']  = $hrs[0];
            $slots[$key]['close'] = $hrs[1];
        }
        $slots = array_values($slots);

        // Closed days as a final "Closed" slot, if any.
        $closed = array_values(array_diff($order, array_keys($dayHours)));
        if (!empty($closed)) {
            $slots[] = ['days' => $closed, 'open' => 'Closed', 'close' => ''];
        }
        if (empty($slots)) {
            $slots[] = ['days' => $order, 'open' => '10:00 AM', 'close' => '08:00 PM'];
        }

        return view('sales_genio.timings_edit', compact('user', 'business', 'slots', 'order'));
    }

    public function updateTimings(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);
        $order    = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

        $slots = $request->input('slots', []);

        // Collect per-day hours from all open slots (last write wins if a day
        // appears twice), then emit parallel arrays in week order.
        $dayHours = [];
        foreach ($slots as $slot) {
            $open  = $slot['open']  ?? '';
            $close = $slot['close'] ?? '';
            $days  = $slot['days']  ?? [];
            if ($open === '' || $open === 'Closed') {
                continue; // closed slot — those days simply stay out
            }
            foreach ($days as $d) {
                if (in_array($d, $order, true)) {
                    $dayHours[$d] = [$open, $close];
                }
            }
        }

        $businessDays = $openTimes = $closeTimes = [];
        foreach ($order as $d) {
            if (isset($dayHours[$d])) {
                $businessDays[] = $d;
                $openTimes[]    = $dayHours[$d][0];
                $closeTimes[]   = $dayHours[$d][1];
            }
        }

        $business->business_days = $businessDays;
        $business->open_time     = $openTimes;
        $business->close_time    = $closeTimes;
        $business->save();

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Business timings updated successfully.');
    }

    public function editEstablishment($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        return view('sales_genio.establishment_edit', compact('user', 'business'));
    }

    public function updateEstablishment(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'established_month' => 'nullable|integer|between:1,12',
            'established_year'  => 'nullable|digits:4',
        ]);

        $business->established_month = $request->established_month ?: null;
        $business->established_year  = $request->established_year ?: null;
        $business->save();

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Year of establishment updated successfully.');
    }

    public function subcategoriesByCategory(Request $request)
    {
        $categoryId = (int) $request->input('category_id');
        if (!$categoryId) {
            return response()->json([]);
        }
        $subs = \App\Models\Subcategory::where('status', 1)
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]);
        return response()->json($subs);
    }

    public function editContact($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        return view('sales_genio.contact_edit', compact('user', 'business'));
    }

    public function updateContact(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $request->validate([
            'contact_title'   => 'nullable|string|max:10',
            'contact_person'  => 'nullable|string|max:255',
            'designation'     => 'nullable|string|max:100',
            'mobiles'         => 'required|array|min:1',
            'mobiles.*'       => 'nullable|digits:10',
            'primary_mobile'  => 'nullable|digits:10',
            'whatsapps'       => 'nullable|array',
            'whatsapps.*'     => 'nullable|digits:10',
            'emails'          => 'nullable|array',
            'emails.*'        => 'nullable|email|max:255',
            'landlines'       => 'nullable|array',
            'landlines.*'     => 'nullable|string|max:20',
            'tollfrees'       => 'nullable|array',
            'tollfrees.*'     => 'nullable|string|max:20',
        ]);

        $clean = fn ($arr) => array_values(array_unique(array_filter(array_map('trim', $arr ?? []))));

        $mobiles = $clean($request->mobiles);
        if (empty($mobiles)) {
            return back()->withErrors(['mobiles' => 'At least one mobile number is required.'])->withInput();
        }

        // Put the chosen primary first so mobile + mobile_numbers[0] agree.
        $primary = $request->primary_mobile;
        if ($primary && in_array($primary, $mobiles, true)) {
            $mobiles = array_merge([$primary], array_values(array_diff($mobiles, [$primary])));
        }

        $emails = $clean($request->emails);

        $business->contact_title     = $request->contact_title;
        $business->contact_person    = $request->contact_person;
        $business->designation       = $request->designation;
        $business->mobile            = $mobiles[0];
        $business->mobile_numbers    = $mobiles;
        $business->whatsapp_numbers  = $clean($request->whatsapps);
        $business->email             = $emails[0] ?? null;
        $business->extra_emails      = array_values(array_slice($emails, 1));
        $business->landline_numbers  = $clean($request->landlines);
        $business->toll_free_numbers = $clean($request->tollfrees);
        $business->save();

        return redirect()->route('sales_genio.business.profile', $business->id)
            ->with('success', 'Contact details updated successfully.');
    }

    public function photoGallery($id)
    {
        $user     = Auth::guard('sales')->user();
        $business = \App\Models\Listing::findOrFail($id);
        $photos   = $this->listingPhotos($business);
        return view('sales_genio.photo_gallery', compact('user', 'business', 'photos'));
    }

    public function uploadPhotos(Request $request, $id)
    {
        $business = \App\Models\Listing::findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'files'   => 'required|array',
            'files.*' => 'file|mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/x-msvideo,video/webm|max:102400', // images or videos, up to 100MB
        ], [
            'files.*.mimetypes' => 'Only images (JPG, PNG, WEBP) or videos (MP4, MOV) are allowed.',
            'files.*.max'       => 'Each file must be under 100MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $photoDir = 'assets/images/listing/photos';
        $videoDir = 'assets/images/listing/videos';
        foreach ([$photoDir, $videoDir] as $d) {
            if (!is_dir(public_path($d))) mkdir(public_path($d), 0755, true);
        }

        $photos = $business->photos ?? [];
        $videos = $business->videos ?? [];
        $saved  = [];

        try {
            foreach ($request->file('files') as $file) {
                $mime    = (string) $file->getMimeType();
                $isVideo = str_starts_with($mime, 'video/');

                if ($isVideo) {
                    $ext      = strtolower($file->getClientOriginalExtension() ?: 'mp4');
                    $filename = uniqid('sg_') . '_' . time() . '.' . $ext;
                    $file->move(public_path($videoDir), $filename);
                    $videos[] = $filename;
                    $saved[] = ['id' => $filename, 'url' => asset($videoDir . '/' . $filename), 'type' => 'video'];
                } else {
                    $filename = uniqid('sg_') . '_' . time() . '.webp';
                    $this->resizeToWebp($file->getRealPath(), public_path($photoDir) . '/' . $filename);
                    $photos[] = $filename;
                    $saved[] = ['id' => $filename, 'url' => asset($photoDir . '/' . $filename), 'type' => 'photo'];
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SalesGenio media upload failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Could not process the file. Try again.'], 500);
        }

        $business->photos = $photos;
        $business->videos = $videos;
        $business->save();

        return response()->json([
            'status' => true,
            'saved'  => $saved,
            'total'  => $this->mediaCount($business),
        ]);
    }

    /**
     * Load an uploaded image, scale it down to at most 1200px on the long
     * edge, and save it as WebP (quality 80). Keeps stored files small so the
     * gallery and cover load fast. Falls back to a plain copy if GD can't read
     * the source.
     */
    private function resizeToWebp(string $srcPath, string $destPath, int $max = 1200, int $quality = 80): void
    {
        $info = @getimagesize($srcPath);
        if (!$info) {
            copy($srcPath, $destPath);
            return;
        }

        [$w, $h] = $info;
        $mime = $info['mime'] ?? '';

        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($srcPath),
            'image/png'  => @imagecreatefrompng($srcPath),
            'image/webp' => @imagecreatefromwebp($srcPath),
            'image/gif'  => @imagecreatefromgif($srcPath),
            default      => null,
        };

        if (!$src) {
            copy($srcPath, $destPath);
            return;
        }

        $scale = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($nw, $nh);
        // White background so transparent PNGs don't turn black in webp.
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        imagewebp($dst, $destPath, $quality);

        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * Saves the still frame the gallery grabbed out of a video, so the thumbnail
     * shows up straight away next time instead of a black square. There is no
     * ffmpeg on the servers, so the browser does the grabbing.
     */
    public function videoPoster(Request $request, $id)
    {
        $listing  = \App\Models\Listing::findOrFail($id);
        $filename = basename((string) $request->input('filename')); // no path traversal

        if (!in_array($filename, $listing->videos ?? [], true)) {
            return response()->json(['status' => false], 404);
        }

        // data:image/webp;base64,....
        $data = (string) $request->input('frame');
        if (!preg_match('#^data:image/(webp|jpeg|png);base64,#', $data)) {
            return response()->json(['status' => false], 422);
        }

        $binary = base64_decode(substr($data, strpos($data, ',') + 1), true);
        if ($binary === false || strlen($binary) < 512) {
            return response()->json(['status' => false], 422);
        }

        $dir = public_path(self::VIDEO_POSTER_DIR);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return response()->json(['status' => false], 500);
        }

        $poster = self::VIDEO_POSTER_DIR . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $tmp    = tempnam(sys_get_temp_dir(), 'poster');
        file_put_contents($tmp, $binary);

        // Normalise whatever the browser sent into a small webp.
        $this->resizeToWebp($tmp, public_path($poster), 640, 75);
        @unlink($tmp);

        return response()->json(['status' => true, 'url' => asset($poster)]);
    }

    /**
     * Makes a gallery photo the listing's cover, and puts the old cover back
     * into the gallery as an ordinary photo — swapping them, never deleting.
     * The two live in different folders, so the files are moved across, each
     * under a fresh name (Cloudflare caches assets for a year by filename).
     */
    public function setCoverPhoto(Request $request, $id)
    {
        $listing  = \App\Models\Listing::findOrFail($id);
        $filename = basename((string) $request->input('filename')); // no path traversal

        $photos = $listing->photos ?? [];
        $key    = array_search($filename, $photos, true);

        if ($key === false) {
            return response()->json(['status' => false, 'message' => 'That photo is not in this gallery.'], 404);
        }

        $photoDir = public_path('assets/images/listing/photos');
        $thumbDir = public_path('assets/images/listing/thumb');

        if (!is_file($photoDir . '/' . $filename)) {
            return response()->json(['status' => false, 'message' => 'The photo file is missing.'], 404);
        }

        if (!is_dir($thumbDir) && !@mkdir($thumbDir, 0755, true)) {
            return response()->json(['status' => false, 'message' => 'Could not write the cover image.'], 500);
        }

        $ext      = pathinfo($filename, PATHINFO_EXTENSION) ?: 'webp';
        $newThumb = time() . '_' . uniqid() . '.' . $ext;

        if (!@copy($photoDir . '/' . $filename, $thumbDir . '/' . $newThumb)) {
            return response()->json(['status' => false, 'message' => 'Could not write the cover image.'], 500);
        }

        // The photo is now the cover, so it leaves the gallery list.
        unset($photos[$key]);
        $photos = array_values($photos);

        // The old cover is NOT thrown away — it rejoins the gallery.
        $oldThumb = $listing->thumb;
        if (!empty($oldThumb) && is_file($thumbDir . '/' . $oldThumb)) {
            $oldExt  = pathinfo($oldThumb, PATHINFO_EXTENSION) ?: 'webp';
            $asPhoto = time() . '_' . uniqid() . '.' . $oldExt;

            if (@copy($thumbDir . '/' . $oldThumb, $photoDir . '/' . $asPhoto)) {
                array_unshift($photos, $asPhoto);
                @unlink($thumbDir . '/' . $oldThumb);
            }
        }

        $listing->thumb  = $newThumb;
        $listing->photos = $photos;
        $listing->save();

        @unlink($photoDir . '/' . $filename);

        return response()->json(['status' => true]);
    }

    public function deletePhoto(Request $request, $id)
    {
        $listing  = \App\Models\Listing::findOrFail($id);
        $filename = basename((string) $request->input('filename')); // no path traversal

        $photos = $listing->photos ?? [];
        $videos = $listing->videos ?? [];

        // The cover image, deleted from the gallery.
        if ($filename !== '' && $filename === $listing->thumb) {
            $listing->thumb = null;
            $listing->save();

            $full = public_path('assets/images/listing/thumb/' . $filename);
            if (is_file($full)) @unlink($full);

            return response()->json(['status' => true, 'total' => $this->mediaCount($listing)]);
        }

        if (($key = array_search($filename, $photos, true)) !== false) {
            unset($photos[$key]);
            $listing->photos = array_values($photos);
            $listing->save();
            $full = public_path('assets/images/listing/photos/' . $filename);
            if (is_file($full)) @unlink($full);
        } elseif (($key = array_search($filename, $videos, true)) !== false) {
            unset($videos[$key]);
            $listing->videos = array_values($videos);
            $listing->save();
            $full = public_path('assets/images/listing/videos/' . $filename);
            if (is_file($full)) @unlink($full);

            $poster = public_path(self::VIDEO_POSTER_DIR . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp');
            if (is_file($poster)) @unlink($poster);
        }

        return response()->json(['status' => true, 'total' => $this->mediaCount($listing)]);
    }

    public function areaSearch(Request $request)
    {
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $select = fn ($like) => \App\Models\Listing::query()
            ->whereNotNull('area')->where('area', '<>', '')
            ->whereNotNull('pincode')->where('pincode', '<>', '')
            ->where('area', 'like', $like)
            ->select('area', 'pincode', 'city')
            ->groupBy('area', 'pincode', 'city')
            ->orderBy('area')
            ->limit(15)
            ->get()
            ->map(fn ($r) => ['area' => $r->area, 'pincode' => $r->pincode, 'city' => $r->city])
            ->values();

        // Prefix match first, then anywhere-match as fallback.
        $results = $select($q . '%');
        if ($results->isEmpty()) {
            $results = $select('%' . $q . '%');
        }

        return response()->json($results);
    }

    /**
     * A clean, unique slug like the rest of the site uses: base slug from the
     * name, and only a -1/-2 suffix if that slug is already taken.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($name) ?: 'listing';
        $slug = $base;
        $i = 1;
        while (
            \App\Models\Listing::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function cleanWebsite(?string $website): ?string
    {
        $website = trim((string) $website);
        if ($website === '' || $website === 'www.' || $website === 'http://www.') {
            return null;
        }
        return $website;
    }

    public function logout(Request $request)
    {
        Auth::guard('sales')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('sales_genio.login');
    }

    private function sendOtpWhatsapp(SalesUser $user, string $otp): void
    {
        try {
            $sender     = app(WhatsappSender::class);
            $templateId = trim((string) config('services.gupshup.otp_template_id'));

            if (!$templateId || !$sender->isConfigured()) {
                \Illuminate\Support\Facades\Log::warning('SalesGenio OTP: WhatsApp not configured', ['empcode' => $user->empcode]);
                return;
            }

            $sender->sendTemplate($user->phone, $templateId, [$otp]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SalesGenio OTP send failed', ['error' => $e->getMessage()]);
        }
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        $len    = strlen($digits);
        if ($len < 4) return '****';
        return str_repeat('*', $len - 4) . substr($digits, -4);
    }
}
