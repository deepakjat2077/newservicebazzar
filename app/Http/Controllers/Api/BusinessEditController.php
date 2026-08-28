<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ListingController;
use App\Http\Traits\FileHandler;
use App\Models\ExtraImage;
use App\Models\Listing;
use App\Models\Subcategory;
use Exception;
use Illuminate\Http\Request;

/**
 * "Edit Business Profile" for the mobile app — the section-by-section editor
 * behind the Edit Profile screen (Business Name, Contact, Address, Serviceable
 * Area, Map, Timings, Year of Establishment, Categories, Photos, Catalogue,
 * Website, Social Media).
 *
 * JSON twin of the business-* methods in App\Http\Controllers\ListingController,
 * kept faithful to their rules — most importantly: **name and address changes
 * do NOT apply straight away**, they are saved as pending_* and wait for admin
 * approval, exactly like the website. Everything else applies immediately.
 *
 * All routes run behind `user.api` and every listing is scoped to the token
 * owner (owned()), so nobody can edit a business that isn't theirs.
 */
class BusinessEditController extends Controller
{
    use FileHandler;

    /** All current editable values in one call — for the Edit Profile screen. */
    public function show(Request $request, $id)
    {
        $l = $this->owned($request, $id);

        return response()->json([
            'success' => true,
            'edit'    => [
                'name'                 => $l->name,
                'pending_name'         => $l->pending_name,
                'name_approval_status' => $l->name_approval_status,

                'address'                 => $l->address,
                'pending_address'         => $l->pending_address,
                'address_approval_status' => $l->address_approval_status,

                'contact_title'  => $l->contact_title,
                'contact_person' => $l->contact_person,
                'mobile_numbers'   => array_values($l->mobile_numbers ?? []),
                'whatsapp_numbers' => array_values($l->whatsapp_numbers ?? []),
                'landline_numbers' => array_values($l->landline_numbers ?? []),
                'extra_emails'     => array_values($l->extra_emails ?? []),

                'receive_leads_other_cities' => (bool) $l->receive_leads_other_cities,
                'latitude'  => $l->latitude,
                'longitude' => $l->longitude,

                'business_days' => array_values($l->business_days ?? []),
                'open_time'     => array_values($l->open_time ?? []),
                'close_time'    => array_values($l->close_time ?? []),
                'open_24_hours' => (bool) $l->open_24_hours,

                'established_month' => $l->established_month,
                'established_year'  => $l->established_year,

                'subcategories' => collect($l->subcategory)->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),

                'website'        => $l->website,
                'extra_websites' => array_values($l->extra_websites ?? []),
                'social_links'   => $l->social_links ?? [],
                'youtube_video_url' => $l->youtube_video_url,

                'photos'    => $this->galleryPayload($l),
                'catalogue' => $this->cataloguePayload($l),
            ],
        ]);
    }

    /* --- Name & address: PENDING admin approval (not applied immediately) --- */

    public function updateName(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate(['name' => 'required|string|max:255']);

        $new = trim($request->name);
        $changed = $new !== trim((string) $l->name);

        if ($changed) {
            $l->pending_name = $new;
            $l->name_approval_status = 'pending';
            $l->save();
        }

        return response()->json([
            'success' => true,
            'status'  => $changed ? 'pending' : 'unchanged',
            'message' => $changed
                ? 'Your new business name has been sent for admin approval.'
                : "That's already your business name.",
        ]);
    }

    public function updateAddress(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate(['address' => 'required|string|max:1000']);

        $new = trim($request->address);
        $changed = $new !== trim((string) $l->address);

        if ($changed) {
            $l->pending_address = $new;
            $l->address_approval_status = 'pending';
            $l->save();
        }

        return response()->json([
            'success' => true,
            'status'  => $changed ? 'pending' : 'unchanged',
            'message' => $changed
                ? 'Your new business address has been sent for admin approval.'
                : "That's already your business address.",
        ]);
    }

    /* --- Everything below applies immediately --- */

    public function updateContact(Request $request, $id)
    {
        $l = $this->owned($request, $id);

        $request->validate([
            'contact_title'   => 'nullable|string|max:50',
            'contact_person'  => 'nullable|string|max:255',
            'mobile_numbers'   => 'nullable|array', 'mobile_numbers.*'   => 'nullable|string|max:20',
            'whatsapp_numbers' => 'nullable|array', 'whatsapp_numbers.*' => 'nullable|string|max:20',
            'landline_numbers' => 'nullable|array', 'landline_numbers.*' => 'nullable|string|max:20',
            'mobile_country_code' => 'nullable|array',
            'whatsapp_country_code' => 'nullable|array',
            'landline_country_code' => 'nullable|array',
            'extra_emails'     => 'nullable|array', 'extra_emails.*'     => 'nullable|email|max:255',
        ]);

        $strip = fn ($v) => collect($v ?? [])->map(fn ($x) => trim((string) $x))->filter(fn ($x) => $x !== '')->values();

        $mob = $strip($request->mobile_numbers);
        $wa  = $strip($request->whatsapp_numbers);
        $ll  = $strip($request->landline_numbers);

        $l->contact_title  = $request->contact_title;
        $l->contact_person = $request->contact_person;
        $l->mobile_numbers        = $mob->all();
        $l->mobile_country_code   = array_slice((array) $request->mobile_country_code, 0, $mob->count());
        $l->whatsapp_numbers      = $wa->all();
        $l->whatsapp_country_code = array_slice((array) $request->whatsapp_country_code, 0, $wa->count());
        $l->landline_numbers      = $ll->all();
        $l->landline_country_code = array_slice((array) $request->landline_country_code, 0, $ll->count());
        $l->extra_emails          = $strip($request->extra_emails)->all();
        $l->save();

        return $this->ok('Contact details updated successfully.');
    }

    public function updateServiceableArea(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate(['receive_leads_other_cities' => 'required|in:0,1']);

        $l->receive_leads_other_cities = (bool) $request->receive_leads_other_cities;
        $l->save();

        return $this->ok('Serviceable area preference saved.');
    }

    public function updateMapLocation(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $l->latitude = $request->latitude;
        $l->longitude = $request->longitude;
        $l->save();

        return $this->ok('Map location updated.');
    }

    public function updateTimings(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate([
            'business_days' => 'nullable|array', 'business_days.*' => 'nullable|string|max:20',
            'open_24_hours' => 'nullable|boolean',
            'open_time'  => 'nullable|array', 'open_time.*'  => 'nullable|string|max:50',
            'close_time' => 'nullable|array', 'close_time.*' => 'nullable|string|max:50',
        ]);

        $l->business_days = $request->input('business_days', []);
        $l->open_24_hours = $request->boolean('open_24_hours');

        if ($l->open_24_hours) {
            $l->open_time = [];
            $l->close_time = [];
        } else {
            $open  = collect($request->input('open_time', []));
            $close = collect($request->input('close_time', []));
            // Keep only slots with both ends filled (drop empty "add slot" rows).
            $keep = $open->keys()->filter(fn ($i) => filled($open[$i] ?? null) && filled($close[$i] ?? null));
            $l->open_time  = $keep->map(fn ($i) => $open[$i])->values()->all();
            $l->close_time = $keep->map(fn ($i) => $close[$i])->values()->all();
        }
        $l->save();

        return $this->ok('Business timings updated.');
    }

    public function updateEstablished(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate([
            'established_month' => 'nullable|integer|between:1,12',
            'established_year'  => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
        ]);

        $l->established_month = $request->established_month ?: null;
        $l->established_year  = $request->established_year ?: null;
        $l->save();

        return $this->ok('Year of establishment updated.');
    }

    public function updateCategories(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate([
            'subcategory_ids'   => 'nullable|array',
            'subcategory_ids.*' => 'integer|exists:subcategories,id',
        ]);

        $l->subcategory_ids = array_values(array_unique(array_filter((array) $request->subcategory_ids)));
        $l->save();

        return $this->ok('Business categories updated.');
    }

    public function updateWebsite(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate([
            'website'          => 'nullable|url|max:255',
            'extra_websites'   => 'nullable|array',
            'extra_websites.*' => 'nullable|url|max:255',
        ]);

        $l->website = $request->website ?: null;
        $l->pending_website = null;
        $l->website_approval_status = null;
        $l->extra_websites = collect($request->extra_websites ?? [])
            ->map(fn ($u) => trim((string) $u))->filter()->values()->all();
        $l->save();

        return $this->ok('Business website updated.');
    }

    public function updateSocial(Request $request, $id)
    {
        $l = $this->owned($request, $id);

        $rules = [];
        foreach (ListingController::SOCIAL_PLATFORMS as $p) {
            $rules[$p] = 'nullable|array';
            $rules[$p . '.*'] = 'nullable|url|max:255';
        }
        $request->validate($rules);

        $social = [];
        foreach (ListingController::SOCIAL_PLATFORMS as $p) {
            $social[$p] = collect($request->input($p, []))
                ->map(fn ($u) => trim((string) $u))->filter()->values()->all();
        }
        $l->social_links = $social;
        $l->save();

        return $this->ok('Social media links updated.');
    }

    public function updateVideo(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate([
            'youtube_video_url' => ['nullable', 'url', 'regex:/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//'],
        ], ['youtube_video_url.regex' => "That doesn't look like a YouTube link."]);

        $l->youtube_video_url = $request->youtube_video_url ?: null;
        $l->save();

        return $this->ok('Video link saved.');
    }

    /* --- Photos & catalogue (extra_images table) --- */

    public function uploadPhotos(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate([
            'images'   => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        foreach ($request->file('images') as $file) {
            try {
                $stored = $this->uploadFile('image', [
                    'file' => $file, 'path' => 'assets/images/listing/extra/', 'size' => '730x484', 'prevFile' => null,
                ]);
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'Could not process an image.'], 422);
            }
            $img = new ExtraImage();
            $img->listing_id = $l->id;
            $img->type = 'gallery';
            $img->image = $stored;
            $img->save();
        }

        return response()->json(['success' => true, 'message' => 'Photos uploaded.', 'photos' => $this->galleryPayload($l->fresh())]);
    }

    public function setCoverPhoto(Request $request, $id, $imageId)
    {
        $l = $this->owned($request, $id);
        $image = $l->extraImages()->where('id', $imageId)->firstOrFail();

        $l->extraImages()->update(['is_cover' => false]);
        $image->is_cover = true;
        $image->save();

        return $this->ok('Cover photo updated.');
    }

    public function deletePhoto(Request $request, $id, $imageId)
    {
        $l = $this->owned($request, $id);
        $l->extraImages()->where('id', $imageId)->firstOrFail()->delete();

        return $this->ok('Photo removed.');
    }

    public function uploadCatalogue(Request $request, $id)
    {
        $l = $this->owned($request, $id);
        $request->validate([
            'images'   => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        foreach ($request->file('images') as $file) {
            $stored = $this->uploadFile('image', [
                'file' => $file, 'path' => 'assets/images/listing/extra/', 'size' => '1000x1000', 'prevFile' => null,
            ]);
            $img = new ExtraImage();
            $img->listing_id = $l->id;
            $img->type = 'catalogue';
            $img->image = $stored;
            $img->save();
        }

        return response()->json(['success' => true, 'message' => 'Catalogue image(s) uploaded.', 'catalogue' => $this->cataloguePayload($l->fresh())]);
    }

    /* --- helpers --- */

    protected function owned(Request $request, $id): Listing
    {
        return Listing::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
    }

    protected function ok(string $message)
    {
        return response()->json(['success' => true, 'message' => $message]);
    }

    protected function galleryPayload(Listing $l): array
    {
        return $l->extraImages()
            ->where(fn ($q) => $q->where('type', 'gallery')->orWhereNull('type')->orWhere('type', ''))
            ->orderByDesc('is_cover')->latest()->get()
            ->map(fn ($img) => [
                'id'       => $img->id,
                'image'    => asset('assets/images/listing/extra/' . $img->image),
                'is_cover' => (bool) $img->is_cover,
            ])->values()->all();
    }

    protected function cataloguePayload(Listing $l): array
    {
        return $l->extraImages()->where('type', 'catalogue')->latest()->get()
            ->map(fn ($img) => [
                'id'    => $img->id,
                'image' => asset('assets/images/listing/extra/' . $img->image),
            ])->values()->all();
    }
}
