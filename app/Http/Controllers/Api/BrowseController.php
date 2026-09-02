<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Rating;
use App\Models\RatingComment;
use App\Models\RatingLike;
use App\Models\Subcategory;
use Illuminate\Http\Request;

/**
 * Consumer discovery for the mobile app — the "find a business" side.
 *
 * JSON twin of the public website (App\Http\Controllers\SiteController):
 * home, category grid, search with filters/sort, business detail with its
 * reviews, plus writing a review, liking/commenting on one, sending an
 * enquiry, and location search. The app is login-first, so every route runs
 * behind `user.api` — which also lets each business card carry whether the
 * current user has favourited it.
 *
 * Search filters/sort mirror SiteController@search exactly (verified / trust /
 * top_rated, sort by rating / popular / distance). A review from a signed-in
 * user is saved straight away with no OTP — the login already proved the
 * phone — which is the logged-in branch of ListingController@review.
 */
class BrowseController extends Controller
{
    /* ---------------------------------------------------------------------
     | Home & categories
     | -------------------------------------------------------------------*/

    public function home(Request $request)
    {
        $favIds = $this->favoriteIds($request);

        $verified = fn ($q) => $q->where('is_verified', 1);

        $featured = Listing::notExpired()
            ->where('status', 1)->where('trust', 1)
            ->with('category', 'location')
            ->orderByDesc('id')->limit(12)->get();

        return response()->json([
            'success'    => true,
            'categories' => Category::where('status', 1)
                ->get(['id', 'name', 'slug', 'image', 'icon'])
                ->map(fn ($c) => $this->categoryPayload($c)),
            'featured'   => $featured->map(fn ($l) => $this->listingCard($l, $favIds))->values(),
            'banners'    => Banner::where('status', 1)->orderBy('sort_order')->latest()->get()
                ->map(fn ($b) => [
                    'title'  => $b->title,
                    'image'  => $b->desktop_banner ? asset('assets/images/category/banner/' . $b->desktop_banner) : null,
                    'mobile' => $b->mobile_banner ? asset('assets/images/category/banner/' . $b->mobile_banner) : null,
                    'link'   => $b->link,
                ]),
            'locations'  => Location::where('status', 1)->orderByDesc('id')->get(['id', 'name', 'slug'])
                ->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'slug' => $l->slug]),
        ]);
    }

    public function categories()
    {
        return response()->json([
            'success'    => true,
            'categories' => Category::where('status', 1)->orderBy('name')
                ->get(['id', 'name', 'slug', 'image', 'icon', 'description'])
                ->map(fn ($c) => $this->categoryPayload($c)),
        ]);
    }

    public function subcategories(string $slug)
    {
        $category = Category::where('status', 1)->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success'       => true,
            'category'      => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug],
            'subcategories' => Subcategory::where('status', 1)->where('category_id', $category->id)
                ->orderBy('name')->get(['id', 'name', 'slug'])
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug]),
        ]);
    }

    public function searchCategories(Request $request, $term = null)
    {
        // Support both /api/categories/search?q=term and /api/categories/search/term
        $q = $term ?? trim((string) $request->query('q'));

        return response()->json([
            'success' => true,
            'results' => $q === '' ? [] : Category::where('status', 1)
                ->where('name', 'like', "%{$q}%")->limit(10)->get(['id', 'name', 'slug'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug]),
        ]);
    }

    public function searchLocations(Request $request, $term = null)
    {
        // Support both /api/locations/search?q=term and /api/locations/search/term
        $q = $term ?? trim((string) $request->query('q'));

        return response()->json([
            'success' => true,
            'results' => $q === '' ? [] : Location::where('status', 1)
                ->where('name', 'like', "%{$q}%")->limit(10)->get(['id', 'name', 'slug'])
                ->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'slug' => $l->slug]),
        ]);
    }

    /* ---------------------------------------------------------------------
     | Search
     | -------------------------------------------------------------------*/

    public function search(Request $request, $location = null, $term = null)
    {
        // Support multiple formats:
        // 1. /api/find?q=plumber&location=delhi (old query params)
        // 2. /api/find/plumber (path term only)
        // 3. /api/find/delhi/plumber (path location/term - slug or search term)

        // Handle the case where location is actually the term (when called as /api/find/{term})
        if ($term === null && $location !== null) {
            $term = $location;
            $location = null;
        }

        // Convert hyphens to spaces for better search
        $term     = str_replace('-', ' ', $term ?? trim((string) $request->query('q', '')));
        $location = str_replace('-', ' ', $location ?? trim((string) $request->query('location', '')));
        $lat      = $request->filled('latitude') ? (float) $request->latitude : null;
        $lng      = $request->filled('longitude') ? (float) $request->longitude : null;

        $listings = Listing::notExpired()->where('status', 1)
            ->with('category', 'location')->withCount('ratings');

        // Match by category slug first (for URL-friendly search like /api/find/delhi/packers-and-movers)
        if ($term !== '') {
            // First try to match by category slug (convert back to check slug)
            $termSlug = str_replace(' ', '-', strtolower($term));
            $categoryBySlug = Category::where('status', 1)->where('slug', $termSlug)->first();

            if ($categoryBySlug) {
                // If term matches a category slug, return listings from that category
                $listings->where('category_id', $categoryBySlug->id);
            } else {
                // Otherwise, do text search (name/category/subcategory match)
                $catIds = Category::where('status', 1)->where('name', 'like', "%{$term}%")->pluck('id')->toArray();
                $subIds = Subcategory::where('status', 1)->where('name', 'like', "%{$term}%")->pluck('id')->toArray();
                $subCatIds = Subcategory::where('status', 1)->where('name', 'like', "%{$term}%")->pluck('category_id')->toArray();

                $listings->where(function ($q) use ($term, $catIds, $subIds, $subCatIds) {
                    if ($catIds)    $q->orWhereIn('category_id', $catIds);
                    if ($subCatIds) $q->orWhereIn('category_id', $subCatIds);
                    if ($subIds) {
                        $q->orWhere(function ($sq) use ($subIds) {
                            foreach ($subIds as $id) $sq->orWhereJsonContains('subcategory_ids', (int) $id);
                        });
                    }
                    $q->orWhere('name', 'like', "%{$term}%");
                });
            }
        }

        // Distance (needs coords) or plain location-text match.
        if ($lat !== null && $lng !== null) {
            $listings->whereNotNull('latitude')->where('latitude', '!=', '')
                ->whereNotNull('longitude')->where('longitude', '!=', '')
                ->selectRaw(
                    'listings.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$lat, $lng, $lat]
                )
                ->having('distance', '<=', 20);
        } elseif ($location !== '') {
            $listings->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                    ->orWhere('area', 'like', "%{$location}%")
                    ->orWhere('address', 'like', "%{$location}%")
                    ->orWhereHas('location', fn ($lq) => $lq->where('name', 'like', "%{$location}%"));
            });
        }

        if ($request->filled('rating')) {
            $listings->where('review', '>=', $request->rating);
        }

        // Clone query for filter attempt
        $filteredListings = clone $listings;

        switch ($request->query('filter')) {
            case 'verified':  $filteredListings->where('verified', 1); break;
            case 'trust':     $filteredListings->where('trust', 1); break;
            case 'top_rated': $filteredListings->where('review', '>=', 4); break;
        }

        // Use filtered results if they exist, otherwise use unfiltered
        if ($request->filled('filter') && $filteredListings->count() > 0) {
            $listings = $filteredListings;
        } elseif (!$request->filled('filter')) {
            $listings = $filteredListings;
        }

        switch ($request->query('sort')) {
            case 'rating':     $listings->orderByDesc('review'); break;
            case 'low_rating': $listings->orderBy('review'); break;
            case 'popular':    $listings->orderByDesc('view'); break;
            default:
                if ($lat !== null && $lng !== null) {
                    $listings->orderBy('distance');
                } else {
                    $listings->orderByRaw('CASE WHEN top_rank IS NULL THEN 1 ELSE 0 END')
                        ->orderBy('top_rank')->orderByDesc('id');
                }
        }

        $page   = $listings->paginate(15)->withQueryString();
        $favIds = $this->favoriteIds($request);

        return response()->json([
            'success'  => true,
            'listings' => collect($page->items())->map(fn ($l) => $this->listingCard($l, $favIds))->values(),
            'meta'     => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Listing detail + reviews
     | -------------------------------------------------------------------*/

    public function detail(Request $request, string $slug)
    {
        $listing = Listing::notExpired()->where('status', 1)->where('slug', $slug)
            ->with('category', 'location', 'ratings.user', 'ratings.comments.user')
            ->withCount('ratings')
            ->firstOrFail();

        $listing->increment('view');

        $favIds = $this->favoriteIds($request);
        $myId   = $request->user()->id;

        return response()->json([
            'success' => true,
            'listing' => array_merge($this->listingCard($listing, $favIds), [
                'details'        => $listing->details,
                'pincode'        => $listing->pincode,
                'address'        => $this->composeAddress($listing),
                'latitude'       => $listing->latitude,
                'longitude'      => $listing->longitude,
                'verified'       => (bool) $listing->verified,
                'trust'          => (bool) $listing->trust,
                'established_year'=> $listing->established_year,
                'views'          => (int) $listing->view,
                'photos'         => collect($listing->photos ?? [])
                    ->map(fn ($p) => asset('assets/images/listing/photos/' . $p))->values(),
                'contact'        => [
                    'person'    => $listing->contact_person,
                    'title'     => $listing->contact_title,
                    'mobiles'   => array_values($listing->mobile_numbers ?? []),
                    'whatsapps' => array_values($listing->whatsapp_numbers ?? []),
                    'landlines' => array_values($listing->landline_numbers ?? []),
                    'emails'    => array_values($listing->extra_emails ?? []),
                ],
                'timings' => [
                    'days'       => array_values($listing->business_days ?? []),
                    'open_time'  => array_values($listing->open_time ?? []),
                    'close_time' => array_values($listing->close_time ?? []),
                ],
                'websites'     => array_values($listing->extra_websites ?? []),
                'social_links' => $listing->social_links ?? [],
                'reviews'      => $listing->ratings->map(fn ($r) => $this->reviewPayload($r, $myId))->values(),
            ]),
        ]);
    }

    /** Submit (or update) a review — signed-in only, no OTP needed. */
    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'ratings' => 'required|integer|min:1|max:5',
            'review'  => 'required|string|max:1000',
        ]);

        $listing = Listing::where('status', 1)->findOrFail($id);
        $user    = $request->user();

        if ((int) $listing->user_id === (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot review your own business.',
            ], 422);
        }

        // One review per user per listing — update the existing one if any.
        $rating = Rating::firstOrNew(['listing_id' => $listing->id, 'user_id' => $user->id]);
        $rating->name        = $user->fullname() ?: ($user->username ?: 'User');
        $rating->email       = $user->email;
        $rating->ratings     = $request->ratings;
        $rating->review      = $request->review;
        $rating->is_verified = 1;
        $rating->save();

        // Keep the listing's cached average in step, same as the website.
        $listing->review = Rating::where('listing_id', $listing->id)->where('is_verified', 1)->avg('ratings') ?: 0;
        $listing->save();

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'review'  => $this->reviewPayload($rating->fresh('comments.user'), $user->id),
        ]);
    }

    public function likeReview(Request $request, $ratingId)
    {
        $rating = Rating::findOrFail($ratingId);
        $like   = RatingLike::where('rating_id', $rating->id)->where('user_id', $request->user()->id)->first();

        if ($like) {
            $like->delete();
            if ($rating->likes_count > 0) $rating->decrement('likes_count');
            $liked = false;
        } else {
            RatingLike::create(['rating_id' => $rating->id, 'user_id' => $request->user()->id]);
            $rating->increment('likes_count');
            $liked = true;
        }

        return response()->json(['success' => true, 'liked' => $liked, 'count' => $rating->fresh()->likes_count]);
    }

    public function commentReview(Request $request, $ratingId)
    {
        $request->validate(['comment' => 'required|string|max:1000']);

        $rating  = Rating::findOrFail($ratingId);
        $comment = RatingComment::create([
            'rating_id' => $rating->id,
            'user_id'   => $request->user()->id,
            'comment'   => $request->comment,
        ]);
        $rating->increment('comments_count');

        return response()->json([
            'success'    => true,
            'id'         => $comment->id,
            'count'      => $rating->fresh()->comments_count,
            'name'       => $request->user()->fullname() ?: 'User',
            'comment'    => $comment->comment,
            'can_delete' => true,
        ]);
    }

    public function deleteComment(Request $request, $id)
    {
        $comment = RatingComment::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $rating  = Rating::find($comment->rating_id);
        $comment->delete();
        if ($rating && $rating->comments_count > 0) $rating->decrement('comments_count');

        return response()->json([
            'success'   => true,
            'rating_id' => $rating?->id,
            'count'     => $rating ? $rating->fresh()->comments_count : 0,
        ]);
    }

    /* ---------------------------------------------------------------------
     | Enquiry & click tracking
     | -------------------------------------------------------------------*/

    /** Direct "Enquiry Now" to one business. */
    public function enquiry(Request $request, $id)
    {
        $listing = Listing::where('status', 1)->findOrFail($id);
        $user    = $request->user();

        $data = $request->validate([
            'name'    => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        Enquiry::create([
            'type'        => 'enquiry',
            'listing_id'  => $listing->id,
            'category_id' => $listing->category_id,
            'location_id' => $listing->location_id,
            'name'        => $data['name']  ?? $user->fullname(),
            'phone'       => $data['phone'] ?? $user->mobile,
            'email'       => $data['email'] ?? $user->email,
            'message'     => $data['message'] ?? null,
            'is_read'     => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Your enquiry has been sent.']);
    }

    /** Records a Call / WhatsApp / Direction / Share tap for analytics. */
    public function trackClick(Request $request, $id)
    {
        $request->validate(['type' => 'required|string|max:20']);

        $listing = Listing::find($id);
        if (!$listing) {
            return response()->json(['success' => true]);
        }

        $click = new \App\Models\ListingClick();
        $click->listing_id   = $listing->id;
        $click->listing_name = $listing->name;
        $click->category_id  = $listing->category_id;
        $click->location_id  = $listing->location_id;
        $click->type = [
            'call' => 'call', 'landline' => 'call',
            'whatsapp' => 'whatsapp', 'enquiry' => 'enquiry',
        ][$request->type] ?? 'call';
        $click->ip_address = $request->ip();
        $click->user_agent = $request->userAgent();
        $click->save();

        return response()->json(['success' => true]);
    }

    /* ---------------------------------------------------------------------
     | Payload builders
     | -------------------------------------------------------------------*/

    protected function categoryPayload($c): array
    {
        return [
            'id'    => $c->id,
            'name'  => $c->name,
            'slug'  => $c->slug,
            'icon'  => $c->icon,
            'image' => $c->image ? asset('assets/images/category/' . $c->image) : null,
        ];
    }

    protected function listingCard(Listing $l, array $favIds): array
    {
        return [
            'id'           => $l->id,
            'name'         => $l->name,
            'slug'         => $l->slug,
            'category'     => optional($l->category)->name,
            'category_slug'=> optional($l->category)->slug,
            'area'         => $l->area ?: optional($l->location)->name,
            'city'         => $l->city,
            'thumb'        => $l->thumb ? loadImage('assets/images/listing/thumb/' . $l->thumb) : loadImage('', false),
            'avg_rating'   => round((float) $l->review, 1),
            'review_count' => (int) ($l->ratings_count ?? $l->ratings()->count()),
            'verified'     => (bool) $l->verified,
            'trust'        => (bool) $l->trust,
            'is_favorited' => in_array($l->id, $favIds, true),
        ];
    }

    protected function reviewPayload(Rating $r, $myId): array
    {
        return [
            'id'             => $r->id,
            'name'           => $r->name,
            'rating'         => (int) $r->ratings,
            'review'         => $r->review,
            'likes_count'    => (int) $r->likes_count,
            'comments_count' => (int) $r->comments_count,
            'liked_by_me'    => $r->relationLoaded('likes')
                ? $r->likes->contains('user_id', $myId)
                : RatingLike::where('rating_id', $r->id)->where('user_id', $myId)->exists(),
            'created_at'     => optional($r->created_at)->toIso8601String(),
            'comments'       => $r->relationLoaded('comments')
                ? $r->comments->map(fn ($c) => [
                    'id'         => $c->id,
                    'name'       => optional($c->user)->first_name ? trim($c->user->first_name . ' ' . $c->user->last_name) : 'User',
                    'comment'    => $c->comment,
                    'can_delete' => (int) $c->user_id === (int) $myId,
                ])->values()
                : [],
        ];
    }

    protected function composeAddress(Listing $l): string
    {
        return collect([
            $l->plot_no, $l->building_name, $l->street_name,
            $l->landmark, $l->area, $l->city, $l->state, $l->pincode,
        ])->filter()->implode(', ');
    }

    /** Listing ids the current user has favourited, for is_favorited flags. */
    protected function favoriteIds(Request $request): array
    {
        return $request->user()->favoriteListings()->pluck('listing_id')->toArray();
    }
}
