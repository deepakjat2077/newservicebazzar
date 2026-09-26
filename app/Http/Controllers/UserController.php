<?php

namespace App\Http\Controllers;

use App\Http\Traits\FileHandler;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserFavoriteCategory;
use App\Models\UserFavoriteListing;
use App\Models\UserSchedule;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Support\HttpRetry;
use App\Support\MobileNumber;
use Carbon\Carbon;

class UserController extends Controller
{
    use FileHandler;

	protected $setting;
	protected $theme;

	/**
	 * Fixed grid for the wizard's Favorites step — everyday-need categories
	 * a user might want quick access to, not the site's business-listing
	 * category tree. Key/label drive matching against the real Category
	 * table (see favoriteListings()); icon names are Font Awesome 5 Free
	 * Solid, which is what this theme actually ships — the 6-only names
	 * (fa-user-doctor, fa-basket-shopping, ...) rendered as blank boxes.
	 */
	const FAVORITE_CATEGORIES = [
		'doctor'          => ['label' => 'Doctor', 'icon' => 'fa-user-md'],
		'hospital'        => ['label' => 'Hospital', 'icon' => 'fa-hospital'],
		'grocery_store'   => ['label' => 'Grocery Store', 'icon' => 'fa-shopping-basket'],
		'chemist'         => ['label' => 'Chemist', 'icon' => 'fa-pills'],
		'laundry_service' => ['label' => 'Laundry Service', 'icon' => 'fa-tshirt'],
		'spa_salon'       => ['label' => 'Spa & Salon', 'icon' => 'fa-spa'],
		'car_service'     => ['label' => 'Car Service', 'icon' => 'fa-car'],
		'ac_service'      => ['label' => 'AC Service', 'icon' => 'fa-wind'],
		'water_purifier'  => ['label' => 'Water Purifier Service', 'icon' => 'fa-tint'],
		'plumber'         => ['label' => 'Plumber', 'icon' => 'fa-wrench'],
		'restaurant'      => ['label' => 'Restaurant', 'icon' => 'fa-utensils'],
		'pizza'           => ['label' => 'Pizza', 'icon' => 'fa-pizza-slice'],
		'cinema_hall'     => ['label' => 'Cinema Hall', 'icon' => 'fa-film'],
	];

	public function __construct(){
		$this->setting = Setting::first();
		$this->theme = 'themes.'.$this->setting->theme.'.';
	}

    public function dashboard(){
        $user = auth()->user();

        // The user's own public profile is their home page now. Same handle the
        // header's profile link uses; a user with no name to build one from
        // keeps the old dashboard rather than hitting a broken URL.
        $handle = \Illuminate\Support\Str::slug($user->first_name . '-' . $user->last_name);
        if ($handle === '' && preg_match('/^[a-z0-9_-]+$/', (string) $user->username)) {
            $handle = $user->username;
        }
        if ($handle !== '') {
            return redirect()->route('user.profile.view', ['username' => $handle]);
        }

        $data['user'] = $user;
    	return view($this->theme.'user.dashboard',$data);
    }

    public function profile()
    {
        $user = auth()->user()->load(['addresses', 'favoriteCategories', 'favoriteListings.listing']);

        $favoriteCategories = self::FAVORITE_CATEGORIES;
        $favoritedListingIds = $user->favoriteListings->pluck('listing_id')->toArray();
        $favoritedCategoryKeys = $user->favoriteCategories
            ->pluck('category_key')
            ->merge($user->favoriteListings->pluck('category_key')->filter())
            ->unique()
            ->values()
            ->toArray();
        $progress = $this->profileWizardProgress($user);
        $initialStep = (int) session('profile_step', 0);

        return view($this->theme.'user.profile', compact('user', 'favoriteCategories', 'favoritedListingIds', 'favoritedCategoryKeys', 'progress', 'initialStep'));
    }

    /**
     * Real businesses to show under a Favorites category card — matched
     * against the site's own Category table by name rather than a fixed
     * id, since the fixed grid (FAVORITE_CATEGORIES) is a generic
     * everyday-need list that doesn't necessarily line up 1:1 with however
     * categories happen to be named in this install.
     */
    public function favoriteListings(Request $request)
    {
        $request->validate([
            'category_key' => 'required|string|in:' . implode(',', array_keys(self::FAVORITE_CATEGORIES)),
        ]);

        $label = self::FAVORITE_CATEGORIES[$request->category_key]['label'];

        $category = Category::where('status', 1)
            ->where('name', 'like', '%' . $label . '%')
            ->first();

        $listings = collect();

        if ($category) {
            $listingQuery = Listing::notExpired()
                ->where('status', 1)
                ->where('category_id', $category->id)
                ->with('location');

            $userLocationId = session('selected_location_id');

            if ($userLocationId) {
                $scoped = (clone $listingQuery)->where('location_id', $userLocationId)->take(20)->get();
                $listings = $scoped->isNotEmpty() ? $scoped : $listingQuery->take(20)->get();
            } else {
                $listings = $listingQuery->take(20)->get();
            }
        }

        $favoritedListingIds = auth()->user()->favoriteListings()->pluck('listing_id')->toArray();

        return response()->json([
            'category' => $label,
            'matched' => (bool) $category,
            'listings' => $listings->map(fn ($listing) => [
                'id' => $listing->id,
                'name' => $listing->name,
                'area' => optional($listing->location)->name,
                'is_favorited' => in_array($listing->id, $favoritedListingIds),
            ]),
        ]);
    }

    public function favoriteListingToggle(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'category_key' => 'nullable|string',
        ]);

        $user = auth()->user();
        $existing = $user->favoriteListings()->where('listing_id', $request->listing_id)->first();

        if ($existing) {
            $existing->delete();
            $selected = false;
        } else {
            $user->favoriteListings()->create([
                'listing_id' => $request->listing_id,
                'category_key' => $request->category_key,
            ]);
            $selected = true;
        }

        $categorySelected = $request->category_key
            ? $user->favoriteListings()->where('category_key', $request->category_key)->exists()
                || $user->favoriteCategories()->where('category_key', $request->category_key)->exists()
            : false;

        return response()->json([
            'selected' => $selected,
            'category_selected' => $categorySelected,
        ]);
    }

    /**
     * How far the wizard sidebar should show as complete: personal details
     * are always step 1's own form (already required on save), addresses
     * unlock once at least one is saved, favorites once at least one pick
     * is saved.
     */
    protected function profileWizardProgress(User $user): array
    {
        $hasPersonal = filled($user->first_name) && filled($user->last_name);
        $hasAddress = $user->addresses()->exists();
        $hasFavorite = $user->favoriteListings()->exists() || $user->favoriteCategories()->exists();

        $completedSteps = collect([$hasPersonal, $hasAddress, $hasFavorite])->filter()->count();
        $percent = (int) round(($completedSteps / 3) * 100);

        return [
            'hasPersonal' => $hasPersonal,
            'hasAddress' => $hasAddress,
            'hasFavorite' => $hasFavorite,
            'isComplete' => $completedSteps === 3,
            'percent' => min(100, $percent),
        ];
    }

    public function search(Request $request)
{
    $q = trim($request->q);

    $users = User::query()
        ->when($q, function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('mobile', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,'')) LIKE ?", ["%{$q}%"]);
            });
        })
        ->select('id', 'first_name', 'last_name', 'username', 'email', 'mobile')
        ->limit(10)
        ->get()
        ->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->username ?? 'User'),
                'username' => $u->username ?? '',
                'email' => $u->email ?? '',
                'mobile' => $u->mobile ?? '',
            ];
        })
        ->values();

    return response()->json($users);
}


    public function profileUpdate(Request $request){
    	$user = auth()->user();

        // The email field is editable only while the account has nothing
        // verified in it — a WhatsApp OTP login records no address at all, so
        // this is where its owner adds one. An address they already confirmed
        // is left alone (the form renders that one read-only).
        $canEditEmail = !$user->hasMailableEmail() || !$user->email_verified;

    	$request->validate(array_filter([
    		'first_name'=>'required',
    		'last_name'=>'required',
            'dob' => ['required', 'string', 'max:10'],
    		'image'=>'nullable|image|mimes:jpg,jpeg,png',
    		'email'=> $canEditEmail
    			? ['nullable','email','max:191',Rule::unique('users','email')->ignore($user->id)]
    			: null,
    	]));

        $dob = $this->normalizeDob($request->dob);

        if ($dob === null) {
            return back()
                ->withInput()
                ->withErrors(['dob' => 'Please enter a valid date of birth.']);
        }

    	$image = $user->image;
    	if ($request->hasFile('image')) {
    		try {
	            $imageData = [
	                'file'=>$request->image,
	                'path'=>'assets/images/user/',
	                'size'=>'300x300',
	                'prevFile'=>$user->image,
	            ];
	            $image = $this->uploadFile('image',$imageData);
    		} catch (Exception $e) {
    			$alert = ['danger','Something wrong'];
    			return back()->withAlert($alert);
    		}
        }

        $user->title = $request->title;
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->address = $request->address;
        $user->website = $request->website;
        $user->dob = $dob;
        $user->marital_status = $request->marital_status;
        $user->city = $request->city;
        $user->area = $request->area;
        $user->pincode = $request->pincode;
        $user->occupation = $request->occupation;
        $mobile2 = MobileNumber::national($request->mobile_2);
        if ($mobile2 !== '') {
            if ($mobile2 !== (string) $user->mobile_2) {
                return back()->withAlert(['danger', 'Please verify Mobile Number 2 before saving your profile.']);
            }
        } elseif (!empty($user->mobile_2)) {
            $user->mobile_2 = null;
            if (Schema::hasColumn('users', 'mobile_2_verified')) {
                $user->mobile_2_verified = 0;
            }
        }
        if ($canEditEmail && $request->filled('email')) {
            $email = trim($request->email);

            if ($email !== (string) $user->email) {
                $user->email = $email;
                // Newly typed in, so nobody has confirmed it yet. The account
                // still gets through the `verified` middleware on the strength
                // of its WhatsApp OTP (see App\Http\Middleware\Verification).
                $user->email_verified = 0;
            }
        }
        $user->image = $image;
        $user->save();

        $alert = ['success','Profile updated successfully'];
        return back()->withAlert($alert);
    }

    protected function normalizeDob($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $exception) {
                continue;
            }
        }

        return null;
    }

    public function sendMobile2Otp(Request $request)
    {
        $request->validate([
            'mobile_2' => ['required', 'digits:10'],
        ]);

        $user = auth()->user();
        $mobile = MobileNumber::national($request->mobile_2);

        if ($mobile === MobileNumber::national($user->mobile)) {
            return response()->json([
                'status' => false,
                'message' => 'Mobile Number 2 must be different from Mobile Number 1.',
            ], 422);
        }

        if ($this->mobile2OwnerExists($mobile, $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'This number is already used by another user.',
            ], 422);
        }

        $apiKey = trim((string) config('services.gupshup.api_key'));
        $appName = trim((string) config('services.gupshup.app_name'));
        $source = preg_replace('/\D/', '', (string) config('services.gupshup.source'));
        $templateId = trim((string) config('services.gupshup.otp_template_id'));

        if ($apiKey === '' || $appName === '' || $source === '' || $templateId === '') {
            Log::error('Gupshup Mobile Number 2 OTP configuration is incomplete.', [
                'api_key_exists' => $apiKey !== '',
                'app_name_exists' => $appName !== '',
                'source_exists' => $source !== '',
                'template_exists' => $templateId !== '',
            ]);

            return response()->json([
                'status' => false,
                'message' => 'WhatsApp configuration is incomplete.',
            ], 500);
        }

        $otp = (string) random_int(100000, 999999);
        $destination = MobileNumber::destination($mobile);

        try {
            $attempt = HttpRetry::send(function () use ($apiKey, $source, $destination, $appName, $templateId, $otp) {
                return Http::asForm()
                    ->connectTimeout(10)
                    ->timeout(30)
                    ->withHeaders([
                        'apikey' => $apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->post('https://api.gupshup.io/wa/api/v1/template/msg', [
                        'channel' => 'whatsapp',
                        'source' => $source,
                        'destination' => $destination,
                        'src.name' => $appName,
                        'template' => json_encode([
                            'id' => $templateId,
                            'params' => [$otp],
                        ], JSON_UNESCAPED_SLASHES),
                    ]);
            }, 3, 1500, 'gupshup.mobile2.otp', 20);

            $response = $attempt['response'];

            if ($response === null) {
                Log::error('Gupshup Mobile Number 2 OTP unreachable', [
                    'destination' => $destination,
                    'attempts' => $attempt['attempts'],
                    'error' => optional($attempt['exception'])->getMessage(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Could not send the OTP right now. Please try again in a moment.',
                ], 502);
            }

            $responseData = $response->json();

            Log::info('Gupshup Mobile Number 2 OTP response', [
                'http_status' => $response->status(),
                'attempts' => $attempt['attempts'],
                'response' => $responseData ?: $response->body(),
                'destination' => $destination,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => false,
                    'message' => HttpRetry::retryable($response)
                        ? 'Could not send the OTP right now. Please try again in a moment.'
                        : ($responseData['message'] ?? 'Gupshup rejected the WhatsApp OTP request.'),
                ], HttpRetry::clientStatus($response->status()));
            }

            $status = strtolower((string) ($responseData['status'] ?? ''));

            if (!in_array($status, ['success', 'submitted'], true)) {
                return response()->json([
                    'status' => false,
                    'message' => $responseData['message'] ?? 'Failed to send WhatsApp OTP.',
                ], 500);
            }

            session([
                'profile_mobile2_number' => $mobile,
                'profile_mobile2_otp' => $otp,
                'profile_mobile2_otp_expires_at' => now()->addMinutes(10)->timestamp,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'OTP has been sent successfully via WhatsApp.',
                'mobile' => $mobile,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Gupshup Mobile Number 2 OTP exception', [
                'exception_class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'destination' => $destination,
            ]);

            return response()->json([
                'status' => false,
                'message' => config('app.debug')
                    ? $exception->getMessage()
                    : 'Unable to connect to the WhatsApp service.',
            ], 500);
        }
    }

    public function verifyMobile2Otp(Request $request)
    {
        $request->validate([
            'mobile_2' => ['required', 'digits:10'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = auth()->user();
        $mobile = MobileNumber::national($request->mobile_2);

        if (!session()->has('profile_mobile2_number') || !session()->has('profile_mobile2_otp')) {
            return response()->json([
                'status' => false,
                'message' => 'Session expired. Please request OTP again.',
            ], 422);
        }

        if ($mobile !== session('profile_mobile2_number')) {
            return response()->json([
                'status' => false,
                'message' => 'Please verify the same number the OTP was sent to.',
            ], 422);
        }

        if (now()->timestamp > session('profile_mobile2_otp_expires_at')) {
            session()->forget(['profile_mobile2_number', 'profile_mobile2_otp', 'profile_mobile2_otp_expires_at']);

            return response()->json([
                'status' => false,
                'message' => 'OTP expired. Please request again.',
            ], 422);
        }

        if (!hash_equals((string) session('profile_mobile2_otp'), (string) $request->otp)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        if ($this->mobile2OwnerExists($mobile, $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'This number is already used by another user.',
            ], 422);
        }

        $user->mobile_2 = $mobile;
        if (Schema::hasColumn('users', 'mobile_2_verified')) {
            $user->mobile_2_verified = 1;
        }
        $user->save();

        session()->forget(['profile_mobile2_number', 'profile_mobile2_otp', 'profile_mobile2_otp_expires_at']);

        return response()->json([
            'status' => true,
            'message' => 'Mobile Number 2 verified successfully.',
            'mobile' => $mobile,
        ]);
    }

    protected function mobile2OwnerExists(string $mobile, int $ignoreUserId): bool
    {
        $variants = MobileNumber::variants($mobile);

        if (empty($variants)) {
            return false;
        }

        $stripMobile = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '')";
        $stripMobile2 = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(mobile_2, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '')";

        return User::where('id', '!=', $ignoreUserId)
            ->where(function ($query) use ($variants, $stripMobile, $stripMobile2) {
                foreach ($variants as $variant) {
                    $query->orWhere('mobile', $variant)
                        ->orWhere('mobile_2', $variant)
                        ->orWhereRaw("$stripMobile = ?", [$variant])
                        ->orWhereRaw("$stripMobile2 = ?", [$variant]);
                }
            })
            ->exists();
    }

    public function addressStore(Request $request)
    {
        $user = auth()->user();

        if ($user->addresses()->exists()) {
            return redirect()
                ->route('user.profile')
                ->with('profile_step', 2)
                ->withAlert(['info', 'Address is already added. Remove it first if you want to add a new one.']);
        }

        $request->validate([
            'address' => 'required|string',
            'landmark' => 'nullable|string|max:191',
            'landline_std' => 'nullable|string|max:10',
            'landline_number' => 'nullable|string|max:20',
            'tag' => 'nullable|string|max:20',
        ]);

        $addressData = [
            'name' => $user->fullname(),
            'contact_number' => $user->mobile,
            'email' => $user->hasMailableEmail() ? $user->email : null,
            'address' => $request->address,
            'landmark' => $request->landmark,
            'pincode' => $user->pincode,
            'city' => $user->city,
            'landline_std' => $request->landline_std,
            'landline_number' => $request->landline_number,
            'tag' => $request->tag,
        ];

        if (Schema::hasColumn('user_addresses', 'area')) {
            $addressData['area'] = $user->area;
        }

        $user->addresses()->create($addressData);

        return redirect()
            ->route('user.profile')
            ->with('profile_step', 2)
            ->withAlert(['success', 'Address added successfully']);
    }

    public function addressDestroy(UserAddress $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $address->delete();

        return back()->withAlert(['success', 'Address removed']);
    }

    public function favoriteToggle(Request $request)
    {
        $request->validate([
            'category_key' => 'required|string|in:' . implode(',', array_keys(self::FAVORITE_CATEGORIES)),
        ]);

        $user = auth()->user();
        $existing = $user->favoriteCategories()->where('category_key', $request->category_key)->first();

        if ($existing) {
            $existing->delete();
            $selected = false;
        } else {
            $user->favoriteCategories()->create(['category_key' => $request->category_key]);
            $selected = true;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['selected' => $selected]);
        }

        return back();
    }

    public function passwordChange(){
		return view($this->theme.'user.password');
	}

    public function passwordUpdate(Request $request){
		$request->validate([
			'old_password'=>'required',
			'password'=>'required|confirmed|min:8',
		]);

		$user = auth()->user();
		if (!Hash::check($request->old_password, $user->password)) {
			$alert = ['danger','Old password doesn\'t match'];
			return back()->withAlert($alert);
		}

		$user->password = Hash::make($request->password);
		$user->save();

		$alert = ['success','Password updated successfully'];
		return back()->withAlert($alert);
	}

    public function social(){
    	$user = auth()->user();
    	return view($this->theme.'user.social',compact('user'));
    }

    public function socialUpdate(Request $request){
    	// Whitelist the known social fields and require each to be a real
    	// http(s) URL — these values are later rendered as raw `href`
    	// attributes on the public author page, so an unvalidated value
    	// (e.g. a `javascript:` URI) would be a stored XSS vector.
    	$request->validate([
    		'facebook'  => ['nullable', 'url', 'regex:/^https?:\/\//i'],
    		'linkedin'  => ['nullable', 'url', 'regex:/^https?:\/\//i'],
    		'youtube'   => ['nullable', 'url', 'regex:/^https?:\/\//i'],
    		'instagram' => ['nullable', 'url', 'regex:/^https?:\/\//i'],
    		'twitter'   => ['nullable', 'url', 'regex:/^https?:\/\//i'],
    	]);

    	$socials = $request->only(['facebook', 'linkedin', 'youtube', 'instagram', 'twitter']);
    	$user = auth()->user();
    	$user->social = $socials;
    	$user->save();
    	$alert = ['success','Social links updated successfully'];
        return back()->withAlert($alert);
    }

    public function schedules(Request $request){
        $store = auth()->user();
        return view($this->theme.'user.schedules',compact('store'));
    }

    public function schedulesAdd(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'day' => 'required|integer|between:0,6',
        ], [
            'end_time.after' => 'End time must be after the start time',
        ]);

        $userId = auth()->user()->id;
        $isOverlapping = UserSchedule::where('user_id', $userId)
            ->where('day', $request->day)
            ->where(function ($query) use ($request) {
                $query->whereBetween('opening_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('closing_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('opening_time', '<=', $request->start_time)
                            ->where('closing_time', '>=', $request->end_time);
                    });
            })->exists();

        if ($isOverlapping) {

            $alert = ['danger','Schedule Overlapping Warning'];
            return back()->withAlert($alert);
        }

        $result = self::insert_schedule(
            user_id: $userId,
            days: [$request->day],
            opening_time: $request->start_time,
            closing_time: $request->end_time . ':59'
        );

        if ($result !== true) {

            $alert = ['danger','Failed to insert schedule'];
            return back()->withAlert($alert);
        }

        $alert = ['success','Schedule added successfully'];
        return back()->withAlert($alert);
    }

    public function scheduleRemoved($id)
    {
        $schedule = UserSchedule::where('id',$id)->where('user_id',auth()->id())->first();
        if(!$schedule)
        {
            $alert = ['danger','Failed to delete schedule'];
            return back()->withAlert($alert);
        }
        $schedule->delete();

        $alert = ['success','Schedule deleted successfully'];
        return back()->withAlert($alert);
    }

    public static function insert_schedule(
        int $user_id,
        array $days = [0, 1, 2, 3, 4, 5, 6],
        string $opening_time = '00:00:00',
        string $closing_time = '23:59:59'
    ): bool|Exception {
        $data = array_map(fn($day) => [
            'user_id' => $user_id,
            'day' => $day,
            'opening_time' => $opening_time,
            'closing_time' => $closing_time
        ], $days);

        try {
            UserSchedule::upsert($data, ['user_id', 'day', 'opening_time', 'closing_time']);
            return true;
        } catch (Exception $e) {
            return $e;
        }
    }

}
