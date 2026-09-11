<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Http\Traits\FileHandler;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserAddress;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The signed-in user's account section for the mobile app.
 *
 * This is the JSON twin of the website's profile wizard and account pages
 * (App\Http\Controllers\UserController): the same "Fill Profile in Few Steps"
 * flow — Personal Details, Addresses, Favorites — plus the two lists the app's
 * account drawer links to: the businesses the user liked and the reviews they
 * left. Every route here runs behind the `user.api` bearer-token middleware,
 * so auth()->user() is always the token's owner.
 *
 * Field names, validation, and the favorites grid are kept identical to the
 * web controller so a value saved from the app and one saved from the website
 * are the same thing — the fixed grid itself is reused straight off
 * UserController::FAVORITE_CATEGORIES rather than copied.
 */
class ProfileController extends Controller
{
    use FileHandler;

    /* ---------------------------------------------------------------------
     | Profile — view & edit personal details
     | -------------------------------------------------------------------*/

    /** The whole account: personal details, addresses, favorites, progress. */
    public function show(Request $request)
    {
        $user = $request->user()->load(['addresses', 'favoriteListings.listing.location']);
        $favoritedIds = $user->favoriteListings->pluck('listing_id')->toArray();

        return response()->json([
            'success'  => true,
            'user'     => $this->userPayload($user),
            'progress' => $this->wizardProgress($user),
            'addresses'=> $user->addresses->map(fn ($a) => $this->addressPayload($a)),
            'favorite_categories' => $this->favoriteCategoryGrid($user),
            'favorite_listings'   => $user->favoriteListings
                ->filter(fn ($f) => $f->listing)
                ->map(fn ($f) => $this->listingCard($f->listing, $favoritedIds))
                ->values(),
        ]);
    }

    /** Save the "Personal details" step. Fields match the website form. */
    public function update(Request $request)
    {
        $user = $request->user();

        // Email is editable only while nothing is verified on the account — a
        // WhatsApp-OTP account records no address, so this is where its owner
        // adds one. Same rule as the website.
        $canEditEmail = !$user->hasMailableEmail() || !$user->email_verified;

        $request->validate(array_filter([
            'first_name' => 'required|string|max:30',
            'last_name'  => 'required|string|max:30',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png',
            'email'      => $canEditEmail
                ? ['nullable', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)]
                : null,
        ]));

        if ($request->hasFile('image')) {
            try {
                $user->image = $this->uploadFile('image', [
                    'file'     => $request->image,
                    'path'     => 'assets/images/user/',
                    'size'     => '300x300',
                    'prevFile' => $user->image,
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not process the image. Please try another.',
                ], 422);
            }
        }

        $user->title          = $request->title;
        $user->first_name     = $request->first_name;
        $user->middle_name    = $request->middle_name;
        $user->last_name      = $request->last_name;
        $user->dob            = $request->filled('dob') ? $request->dob : null;
        $user->marital_status = $request->marital_status;
        $user->city           = $request->city;
        $user->area           = $request->area;
        $user->pincode        = $request->pincode;
        $user->occupation     = $request->occupation;

        if ($request->filled('mobile_2')) {
            $user->mobile_2 = $request->mobile_2;
        }

        if ($canEditEmail && $request->filled('email')) {
            $email = trim($request->email);
            if ($email !== (string) $user->email) {
                $user->email = $email;
                $user->email_verified = 0;
            }
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user'    => $this->userPayload($user->fresh()),
        ]);
    }

    /* ---------------------------------------------------------------------
     | Addresses — "Please provide home and office address"
     | -------------------------------------------------------------------*/

    public function addresses(Request $request)
    {
        return response()->json([
            'success'   => true,
            'addresses' => $request->user()->addresses()->latest()->get()
                ->map(fn ($a) => $this->addressPayload($a)),
        ]);
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'name'            => 'nullable|string|max:191',
            'contact_number'  => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:191',
            'address'         => 'nullable|string',
            'landmark'        => 'nullable|string|max:191',
            'pincode'         => 'nullable|string|max:10',
            'city'            => 'nullable|string|max:191',
            'landline_std'    => 'nullable|string|max:10',
            'landline_number' => 'nullable|string|max:20',
            'tag'             => 'nullable|string|max:20',
        ]);

        $address = $request->user()->addresses()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully.',
            'address' => $this->addressPayload($address),
        ], 201);
    }

    public function destroyAddress(Request $request, UserAddress $address)
    {
        // The owner only — route-model binding does not scope by user.
        abort_unless($address->user_id === $request->user()->id, 403);

        $address->delete();

        return response()->json(['success' => true, 'message' => 'Address removed.']);
    }

    /* ---------------------------------------------------------------------
     | Favorite categories — the wizard's Favorites grid
     | -------------------------------------------------------------------*/

    /** The fixed grid, each card flagged with whether the user picked it. */
    public function favoriteCategories(Request $request)
    {
        return response()->json([
            'success'    => true,
            'categories' => $this->favoriteCategoryGrid($request->user()),
        ]);
    }

    public function toggleFavoriteCategory(Request $request)
    {
        $request->validate([
            'category_key' => 'required|string|in:' . $this->categoryKeys(),
        ]);

        $user = $request->user();
        $existing = $user->favoriteCategories()->where('category_key', $request->category_key)->first();

        if ($existing) {
            $existing->delete();
            $selected = false;
        } else {
            $user->favoriteCategories()->create(['category_key' => $request->category_key]);
            $selected = true;
        }

        return response()->json(['success' => true, 'selected' => $selected]);
    }

    /**
     * Real businesses under a Favorites category card — the "Doctor" list with
     * "Add to Favorite / Added to Favorite" buttons. Matched against the site's
     * Category table by name, exactly like the website does.
     */
    public function categoryListings(Request $request, string $key)
    {
        abort_unless(array_key_exists($key, UserController::FAVORITE_CATEGORIES), 404);

        $label = UserController::FAVORITE_CATEGORIES[$key]['label'];

        $category = Category::where('status', 1)
            ->where('name', 'like', '%' . $label . '%')
            ->first();

        $listings = collect();

        if ($category) {
            $query = Listing::notExpired()
                ->where('status', 1)
                ->where('category_id', $category->id)
                ->with('location');

            // No session on the app; the caller may pass its selected location.
            if ($request->filled('location_id')) {
                $scoped = (clone $query)->where('location_id', $request->location_id)->take(20)->get();
                $listings = $scoped->isNotEmpty() ? $scoped : $query->take(20)->get();
            } else {
                $listings = $query->take(20)->get();
            }
        }

        $favoritedIds = $request->user()->favoriteListings()->pluck('listing_id')->toArray();

        return response()->json([
            'success'  => true,
            'category' => $label,
            'matched'  => (bool) $category,
            'listings' => $listings->map(fn ($l) => $this->listingCard($l, $favoritedIds))->values(),
        ]);
    }

    /* ---------------------------------------------------------------------
     | Favorite listings — the "likes" the account drawer shows
     | -------------------------------------------------------------------*/

    public function favoriteListings(Request $request)
    {
        $favorites = $request->user()->favoriteListings()
            ->with('listing.location', 'listing.category')
            ->latest()
            ->get();

        $ids = $favorites->pluck('listing_id')->toArray();

        return response()->json([
            'success'   => true,
            'listings'  => $favorites
                ->filter(fn ($f) => $f->listing)
                ->map(fn ($f) => $this->listingCard($f->listing, $ids))
                ->values(),
        ]);
    }

    public function toggleFavoriteListing(Request $request)
    {
        $request->validate([
            'listing_id'   => 'required|exists:listings,id',
            'category_key' => 'nullable|string',
        ]);

        $user = $request->user();
        $existing = $user->favoriteListings()->where('listing_id', $request->listing_id)->first();

        if ($existing) {
            $existing->delete();
            $selected = false;
        } else {
            $user->favoriteListings()->create([
                'listing_id'   => $request->listing_id,
                'category_key' => $request->category_key,
            ]);
            $selected = true;
        }

        return response()->json(['success' => true, 'selected' => $selected]);
    }

    /* ---------------------------------------------------------------------
     | Ratings — reviews the user has given
     | -------------------------------------------------------------------*/

    /**
     * Every review this user posted, newest first, each carrying the business
     * it was left on so the app can open that listing's profile.
     */
    public function ratings(Request $request)
    {
        $ratings = Rating::where('user_id', $request->user()->id)
            ->with('listing.location', 'listing.category')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'ratings' => collect($ratings->items())->map(fn ($r) => [
                'id'             => $r->id,
                'rating'         => (int) $r->ratings,
                'review'         => $r->review,
                'likes_count'    => (int) $r->likes_count,
                'comments_count' => (int) $r->comments_count,
                'created_at'     => optional($r->created_at)->toIso8601String(),
                'listing'        => $r->listing ? $this->listingCard($r->listing, []) : null,
            ]),
            'meta' => [
                'current_page' => $ratings->currentPage(),
                'last_page'    => $ratings->lastPage(),
                'total'        => $ratings->total(),
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Payload builders / helpers
     | -------------------------------------------------------------------*/

    protected function userPayload(User $user): array
    {
        return [
            'id'              => $user->id,
            'name'            => $user->fullname(),
            'title'           => $user->title,
            'first_name'      => $user->first_name,
            'middle_name'     => $user->middle_name,
            'last_name'       => $user->last_name,
            'username'        => $user->username,
            'email'           => $user->email,
            'mobile'          => $user->mobile,
            'mobile_2'        => $user->mobile_2,
            'dob'             => $user->dob,
            'marital_status'  => $user->marital_status,
            'occupation'      => $user->occupation,
            'city'            => $user->city,
            'area'            => $user->area,
            'pincode'         => $user->pincode,
            'image'           => $user->image ? loadImageWebp('assets/images/user/' . basename($user->image)) : null,
            'can_edit_email'  => !$user->hasMailableEmail() || !$user->email_verified,
            'mobile_verified' => $user->isMobileVerified(),
            'email_verified'  => (bool) $user->email_verified,
        ];
    }

    protected function addressPayload($a): array
    {
        return [
            'id'              => $a->id,
            'name'            => $a->name,
            'contact_number'  => $a->contact_number,
            'email'           => $a->email,
            'address'         => $a->address,
            'landmark'        => $a->landmark,
            'pincode'         => $a->pincode,
            'city'            => $a->city,
            'landline_std'    => $a->landline_std,
            'landline_number' => $a->landline_number,
            'tag'             => $a->tag,
        ];
    }

    /** Minimal business card, shared by favorites / category lists / reviews. */
    protected function listingCard(Listing $listing, array $favoritedIds): array
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
            'avg_rating'   => round((float) ($listing->ratings()->avg('ratings') ?? 0), 1),
            'review_count' => (int) $listing->ratings()->count(),
            'is_favorited' => in_array($listing->id, $favoritedIds, true),
        ];
    }

    /** The fixed Favorites grid with each card's selected state for this user. */
    protected function favoriteCategoryGrid(User $user): array
    {
        $selected = $user->favoriteCategories()->pluck('category_key')->toArray();

        $grid = [];
        foreach (UserController::FAVORITE_CATEGORIES as $key => $meta) {
            $grid[] = [
                'key'      => $key,
                'label'    => $meta['label'],
                'icon'     => $meta['icon'],
                'selected' => in_array($key, $selected, true),
            ];
        }

        return $grid;
    }

    /** Same 4-step progress the website's wizard sidebar shows. */
    protected function wizardProgress(User $user): array
    {
        $hasPersonal = filled($user->first_name) && filled($user->last_name);
        $hasAddress  = $user->addresses()->exists();
        $hasFavorite = $user->favoriteListings()->exists() || $user->favoriteCategories()->exists();

        $completed = collect([$hasPersonal, $hasAddress, $hasFavorite])->filter()->count();

        return [
            'personal_done'  => $hasPersonal,
            'address_done'   => $hasAddress,
            'favorite_done'  => $hasFavorite,
            'percent'        => (int) round(($completed / 4) * 100),
        ];
    }

    protected function categoryKeys(): string
    {
        return implode(',', array_keys(UserController::FAVORITE_CATEGORIES));
    }
}
