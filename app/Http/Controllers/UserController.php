<?php

namespace App\Http\Controllers;

use App\Http\Traits\FileHandler;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserFavoriteCategory;
use App\Models\UserSchedule;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use FileHandler;

	protected $setting;
	protected $theme;

	/**
	 * Fixed grid for the wizard's Favorites step — everyday-need categories
	 * a user might want quick access to, not the site's business-listing
	 * category tree. Key is what gets stored; label/icon are display-only.
	 */
	const FAVORITE_CATEGORIES = [
		'doctor'          => ['label' => 'Doctor', 'icon' => 'fa-user-doctor'],
		'hospital'        => ['label' => 'Hospital', 'icon' => 'fa-hospital'],
		'grocery_store'   => ['label' => 'Grocery Store', 'icon' => 'fa-basket-shopping'],
		'chemist'         => ['label' => 'Chemist', 'icon' => 'fa-pills'],
		'laundry_service' => ['label' => 'Laundry Service', 'icon' => 'fa-shirt'],
		'spa_salon'       => ['label' => 'Spa & Salon', 'icon' => 'fa-spa'],
		'car_service'     => ['label' => 'Car Service', 'icon' => 'fa-car'],
		'ac_service'      => ['label' => 'AC Service', 'icon' => 'fa-fan'],
		'water_purifier'  => ['label' => 'Water Purifier Service', 'icon' => 'fa-droplet'],
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
        $data['user'] = $user;
    	return view($this->theme.'user.dashboard',$data);
    }

    public function profile()
    {
        $user = auth()->user()->load('addresses');

        $favoriteCategories = self::FAVORITE_CATEGORIES;
        $selectedFavorites = $user->favoriteCategories()->pluck('category_key')->toArray();
        $progress = $this->profileWizardProgress($user);

        return view($this->theme.'user.profile', compact('user', 'favoriteCategories', 'selectedFavorites', 'progress'));
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
        $hasFavorite = $user->favoriteCategories()->exists();

        $completedSteps = collect([$hasPersonal, $hasAddress, $hasFavorite])->filter()->count();

        return [
            'hasPersonal' => $hasPersonal,
            'hasAddress' => $hasAddress,
            'hasFavorite' => $hasFavorite,
            // 4 steps total (Personal, Addresses, Favorites, Completed) —
            // matches the "Overall Progress" bar under the wizard heading.
            'percent' => (int) round(($completedSteps / 4) * 100),
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
    	$request->validate([
    		'first_name'=>'required',
    		'last_name'=>'required',
    		'image'=>'nullable|image|mimes:jpg,jpeg,png',
    	]);
    	$user = auth()->user();

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
        $user->dob = $request->filled('dob') ? $request->dob : null;
        $user->marital_status = $request->marital_status;
        $user->city = $request->city;
        $user->area = $request->area;
        $user->pincode = $request->pincode;
        $user->occupation = $request->occupation;
        $user->image = $image;
        $user->save();

        $alert = ['success','Profile updated successfully'];
        return back()->withAlert($alert);
    }

    public function addressStore(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:191',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string',
            'landmark' => 'nullable|string|max:191',
            'pincode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:191',
            'landline_std' => 'nullable|string|max:10',
            'landline_number' => 'nullable|string|max:20',
            'tag' => 'nullable|string|max:20',
        ]);

        auth()->user()->addresses()->create($request->only([
            'name', 'contact_number', 'email', 'address', 'landmark',
            'pincode', 'city', 'landline_std', 'landline_number', 'tag',
        ]));

        return back()->withAlert(['success', 'Address added successfully']);
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
