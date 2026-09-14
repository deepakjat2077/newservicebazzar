<?php

namespace App\Http\Controllers\Admin;

use App\Support\Images\WebpImage;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileHandler;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\ExtraImage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Listing;
use App\Models\Freelistings;
use App\Models\Location;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use HTMLPurifier;
use App\Support\HttpRetry;
use Illuminate\Http\Client\ConnectionException;

use Illuminate\Http\Request;

class ListingController extends Controller
{
    use FileHandler;

    /**
     * localStorage key the Add Listing form autosaves into. Kept here rather
     * than in the view because the success redirect is what tells the browser
     * the draft can go — and the two have to name the same key.
     */
    protected const ADD_DRAFT_KEY = 'sb.admin.listing.add.draft.v1';

    /** Per admin, so two accounts sharing a machine keep separate drafts. */
    protected static function addDraftKey(): string
    {
        return self::ADD_DRAFT_KEY . '.' . (auth()->guard('admin')->id() ?? 'guest');
    }

    public function index(Request $request){
        $listings = $this->buildListingQuery($request)->paginate(20);
    	$title = 'All Listings';
        $categories = Category::where('status',1)->orderBy('name')->get();
        $locations = Location::where('status',1)->orderBy('name')->get();
    	return view('admin.listing.index',compact('listings','title','categories','locations'));
    }

    public function pending(Request $request){
    	$listings = $this->buildListingQuery($request)->where('status',0)->paginate(20);
    	$title = 'Pending Listings';
        $categories = Category::where('status',1)->orderBy('name')->get();
        $locations = Location::where('status',1)->orderBy('name')->get();
    	return view('admin.listing.index',compact('listings','title','categories','locations'));
    }

    public function approved(Request $request){
    	$listings = $this->buildListingQuery($request)->where('status',1)->paginate(20);
    	$title = 'Approved Listings';
        $categories = Category::where('status',1)->orderBy('name')->get();
        $locations = Location::where('status',1)->orderBy('name')->get();
    	return view('admin.listing.index',compact('listings','title','categories','locations'));
    }

    public function rejected(Request $request){
    	$listings = $this->buildListingQuery($request)->where('status',2)->paginate(20);
    	$title = 'Rejected Listings';
        $categories = Category::where('status',1)->orderBy('name')->get();
        $locations = Location::where('status',1)->orderBy('name')->get();
    	return view('admin.listing.index',compact('listings','title','categories','locations'));
    }

     public function details($id){
    	$listing = Listing::findOrFail($id);
    	return view('admin.listing.details',compact('listing'));
    }

   public function action(Request $request, $id)
{
    $request->validate([
        'status'   => 'required|in:0,1,2',
        'verified' => 'required|in:0,1',
        'trust'    => 'required|in:0,1',
         'is_star'  => 'required|in:0,1',
        'top_rank' => 'nullable|in:1,2,3,4',
        'note'     => 'required',
    ]);

    $listing = Listing::findOrFail($id);

    $listing->status   = $request->status;
    $listing->verified = $request->verified;
    $listing->trust    = $request->trust;
 $listing->is_star  = $request->is_star;
    $listing->note     = $request->note;

    if ($request->status == 1 && $request->filled('top_rank')) {

        // check same rank already exist
        $duplicateRank = Listing::where('category_id', $listing->category_id)
            ->where('top_rank', $request->top_rank)
            ->where('id', '!=', $listing->id)
            ->exists();

        if ($duplicateRank) {
            return back()->withErrors([
                'top_rank' => 'This rank already exists in this category.'
            ])->withInput();
        }

        // check max 4 top listings
        $topCount = Listing::where('category_id', $listing->category_id)
            ->whereNotNull('top_rank')
            ->where('id', '!=', $listing->id)
            ->count();

        if ($topCount >= 4) {
            return back()->withErrors([
                'top_rank' => 'Only 4 top listings allowed per category.'
            ])->withInput();
        }

        $listing->top_rank = $request->top_rank;

    } else {
        $listing->top_rank = null;
    }

    $listing->save();

    $alert = ['success','Listing action taken successfully'];
    return back()->withAlert($alert);
}

public function edit($id)
{
    $listing = Listing::findOrFail($id);
    $categories = Category::where('status', 1)->get();
    $locations = Location::where('status', 1)->get();

    // sirf selected category ki subcategories
    $subcategories = Subcategory::where('status', 1)
        ->where('category_id', $listing->category_id)
        ->get();

    return view('admin.listing.edit', compact(
        'listing',
        'categories',
        'locations',
        'subcategories'
    ));
}

public function update(Request $request, $id)
{
    $listing = Listing::findOrFail($id);

    $rules = [
        'images'                   => 'nullable|array',
        'images.*'                 => 'nullable|image|mimes:jpg,jpeg,png,webp',

        'remove_images'            => 'nullable|array',
        'remove_images.*'          => 'nullable|string',

        'features'                 => 'nullable|array',
        'features.*'               => 'nullable|string|max:255',

        'question'                 => 'nullable|array',
        'question.*'               => 'nullable|string|max:255',

        'answer'                   => 'nullable|array',
        'answer.*'                 => 'nullable|string|max:1000',

        'name'                     => 'required|string|max:255',
        'details'                  => 'nullable|string',

        'meta_title'               => 'nullable|string|max:255',
        'meta_keywords'            => 'nullable|string|max:1000',
        'meta_description'         => 'nullable|string|max:1000',

        'pincode'                  => 'nullable|string|max:10',
        'plot_no'                  => 'nullable|string|max:255',
        'building_name'            => 'nullable|string|max:255',
        'street_name'              => 'nullable|string|max:255',
        'landmark'                 => 'nullable|string|max:255',
        'area'                     => 'nullable|string|max:255',
        'city'                     => 'nullable|string|max:255',
        'state'                    => 'nullable|string|max:255',
        'latitude'                 => 'nullable|numeric',
        'longitude'                => 'nullable|numeric',

        'contact_title'            => 'nullable|string|max:50',
        'contact_person'           => 'nullable|string|max:255',

        'mobile_country_code'      => 'nullable|array',
        'mobile_country_code.*'    => 'nullable|string|max:10',
        'mobile_numbers'           => 'nullable|array',
        'mobile_numbers.*'         => 'nullable|string|max:20',

        'whatsapp_country_code'    => 'nullable|array',
        'whatsapp_country_code.*'  => 'nullable|string|max:10',
        'whatsapp_numbers'         => 'nullable|array',
        'whatsapp_numbers.*'       => 'nullable|string|max:20',

        'landline_country_code'    => 'nullable|array',
        'landline_country_code.*'  => 'nullable|string|max:10',
        'landline_numbers'         => 'nullable|array',
        'landline_numbers.*'       => 'nullable|string|max:20',

        'extra_emails'             => 'nullable|array',
        'extra_emails.*'           => 'nullable|email|max:255',

        'business_days'            => 'nullable|array',
        'business_days.*'          => 'nullable|string|max:20',
        'open_24_hours'            => 'nullable|boolean',

        'open_time'                => 'nullable|array',
        'open_time.*'              => 'nullable|string|max:50',

        'close_time'               => 'nullable|array',
        'close_time.*'             => 'nullable|string|max:50',

        'location_id'              => 'nullable|exists:locations,id',
        'user_id'                  => 'nullable|exists:users,id',
        'category_id'              => 'nullable|exists:categories,id',
        'subcategory_ids'          => 'nullable|array',
        'subcategory_ids.*'        => 'nullable|exists:subcategories,id',

        'image'                    => 'nullable|image|mimes:jpg,jpeg,png,webp',
    ];

    $request->validate($rules);

    $blockedWords = [
        'sex', 'xxx', 'porn', 'adult', 'nude', 'call girl', 'callgirl',
        'escort', 'escorts', 'hot video', 'blue film', 'bluefilm',
        'sex video', 'sexvideo', 'porn video', 'pornvideo', 'sexy',
        'night service', 'prostitute'
    ];

    $cleanText = function ($text) use ($blockedWords) {
        if (is_null($text)) {
            return null;
        }

        $text = (string) $text;
        $text = preg_replace('/https?:\/\/\S+/i', '', $text);
        $text = preg_replace('/www\.\S+/i', '', $text);
        $text = preg_replace('/ftp:\/\/\S+/i', '', $text);
        $text = preg_replace('/mailto:\S+/i', '', $text);
        $text = preg_replace('/tel:\S+/i', '', $text);
        $text = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $text);
        $text = preg_replace('/\s(?:href|src)\s*=\s*("|\').*?\1/i', '', $text);

        foreach ($blockedWords as $word) {
            $text = preg_replace('/' . preg_quote($word, '/') . '/i', '', $text);
        }

        $text = preg_replace('/\s{2,}/', ' ', $text);
        return trim($text);
    };

    $cleanArray = function ($items) use ($cleanText) {
        $items = is_array($items) ? $items : [];
        $cleaned = [];

        foreach ($items as $item) {
            $value = $cleanText($item);
            if (!is_null($value) && $value !== '') {
                $cleaned[] = $value;
            }
        }

        return array_values($cleaned);
    };

    $cleanSimpleArray = function ($items) {
        $items = is_array($items) ? $items : [];

        return array_values(array_filter(array_map(function ($item) {
            return is_string($item) ? trim($item) : $item;
        }, $items), function ($item) {
            return $item !== null && $item !== '';
        }));
    };

    $getValue = function ($field, $oldValue, $cleaner = null) use ($request) {
        if ($request->has($field)) {
            return $cleaner ? $cleaner($request->$field) : $request->$field;
        }

        return $oldValue;
    };

    $name            = $request->has('name') ? $cleanText($request->name) : $listing->name;
    // $listing->details is rendered unescaped ({!! !!}) on the public listing
    // page. $cleanText() only strips a profanity blocklist and href/src
    // attributes via regex — it does not neutralize <script> tags or other
    // event-handler attributes, so HTMLPurifier is applied as the
    // authoritative HTML-safety pass.
    $details         = $request->has('details') ? (new HTMLPurifier())->purify((string) $cleanText($request->details)) : $listing->details;
    $metaTitle       = $request->has('meta_title') ? $cleanText($request->meta_title) : $listing->meta_title;
    $metaKeywords    = $request->has('meta_keywords') ? $cleanText($request->meta_keywords) : $listing->meta_keywords;
    $metaDescription = $request->has('meta_description') ? $cleanText($request->meta_description) : $listing->meta_description;

    // Backfill anything left blank — see fallbackSeoFor()'s docblock.
    [$metaTitle, $metaKeywords, $metaDescription, $details] = $this->fallbackSeoFor(
        $name,
        $request->has('city') ? $request->city : $listing->city,
        $request->filled('category_id') ? $request->category_id : $listing->category_id,
        $metaTitle, $metaKeywords, $metaDescription, $details
    );

    $plotNo          = $request->has('plot_no') ? $cleanText($request->plot_no) : $listing->plot_no;
    $buildingName    = $request->has('building_name') ? $cleanText($request->building_name) : $listing->building_name;
    $streetName      = $request->has('street_name') ? $cleanText($request->street_name) : $listing->street_name;
    $landmark        = $request->has('landmark') ? $cleanText($request->landmark) : $listing->landmark;
    $area            = $request->has('area') ? $cleanText($request->area) : $listing->area;
    $city            = $request->has('city') ? $cleanText($request->city) : $listing->city;
    $state           = $request->has('state') ? $cleanText($request->state) : $listing->state;
    $contactTitle    = $request->has('contact_title') ? $cleanText($request->contact_title) : $listing->contact_title;
    $contactPerson   = $request->has('contact_person') ? $cleanText($request->contact_person) : $listing->contact_person;

    $website   = $request->has('website') ? trim($request->website) : $listing->website;
    $facebook  = $request->has('facebook') ? trim($request->facebook) : $listing->facebook;
    $instagram = $request->has('instagram') ? trim($request->instagram) : $listing->instagram;
    $linkedin  = $request->has('linkedin') ? trim($request->linkedin) : $listing->linkedin;
    $twitter   = $request->has('twitter') ? trim($request->twitter) : $listing->twitter;
    $youtube   = $request->has('youtube') ? trim($request->youtube) : $listing->youtube;

    $features  = $request->has('features') ? $cleanArray($request->features) : ($listing->features ?? []);

    $questions = $request->has('question')
        ? $cleanArray($request->question)
        : ($listing->faq['question'] ?? []);

    $answers = $request->has('answer')
        ? $cleanArray($request->answer)
        : ($listing->faq['answer'] ?? []);

    $mobileCountryCode = $request->has('mobile_country_code')
        ? $cleanSimpleArray($request->mobile_country_code)
        : ($listing->mobile_country_code ?? []);

    $mobileNumbers = $request->has('mobile_numbers')
        ? $cleanSimpleArray($request->mobile_numbers)
        : ($listing->mobile_numbers ?? []);

    $whatsappCountryCode = $request->has('whatsapp_country_code')
        ? $cleanSimpleArray($request->whatsapp_country_code)
        : ($listing->whatsapp_country_code ?? []);

    $whatsappNumbers = $request->has('whatsapp_numbers')
        ? $cleanSimpleArray($request->whatsapp_numbers)
        : ($listing->whatsapp_numbers ?? []);

    $landlineCountryCode = $request->has('landline_country_code')
        ? $cleanSimpleArray($request->landline_country_code)
        : ($listing->landline_country_code ?? []);

    $landlineNumbers = $request->has('landline_numbers')
        ? $cleanSimpleArray($request->landline_numbers)
        : ($listing->landline_numbers ?? []);

    $extraEmails = $request->has('extra_emails')
        ? $cleanSimpleArray($request->extra_emails)
        : ($listing->extra_emails ?? []);

    /*
     * timing_days[] carries one entry per open/close row (see
     * appendDayTimeSlot() in add.blade.php) and is allowed to repeat a day —
     * that repeat IS a second shift for that day. business_days[] (the
     * weekday checkboxes) is deduplicated by nature and used as a fallback
     * for any caller that still only sends that.
     *
     * Both fell back to [] when neither field was present, unlike every
     * other field on this form (mobile/whatsapp/landline numbers, extra
     * emails, all above) which falls back to the listing's current value.
     * That meant editing and saving a listing whose timing rows didn't get
     * resubmitted — e.g. the JS-rendered #timeSlotsWrapper rows silently
     * failing to include their hidden timing_days[] input — wiped the
     * saved business hours instead of leaving them untouched, even though
     * the admin never touched that section.
     */
    $businessDays = $request->has('timing_days')
        ? $cleanSimpleArray($request->timing_days)
        : ($request->has('business_days')
            ? $cleanSimpleArray($request->business_days)
            : ($listing->business_days ?? []));


$isOpen24Hours = $request->boolean('open_24_hours');

if ($isOpen24Hours) {
    $openTime = ['12:00 AM'];
    $closeTime = ['11:59 PM'];
} else {
    $openTime = $request->has('open_time')
        ? $cleanSimpleArray($request->open_time)
        : ($listing->open_time ?? []);

    $closeTime = $request->has('close_time')
        ? $cleanSimpleArray($request->close_time)
        : ($listing->close_time ?? []);
}

    $subcategoryIds = $request->has('subcategory_ids')
        ? array_values(array_filter($request->subcategory_ids ?? []))
        : ($listing->subcategory_ids ?? []);

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $newThumb = null;

        // Convert and write the new file in FIRST, confirming it landed on
        // disk before touching the old one — if it fails (permissions, full
        // disk, interrupted upload) the existing thumb must stay untouched
        // instead of being deleted for a replacement that never happened.
        try {
            $newThumb = WebpImage::store(
                $file,
                'assets/images/listing/thumb',
                time().'_'.uniqid()
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['image' => 'Failed to upload the business image. The existing image was kept unchanged. Please try again.'])
                ->withInput();
        }

        if (!empty($listing->thumb) && file_exists(public_path('assets/images/listing/thumb/' . $listing->thumb))) {
            @unlink(public_path('assets/images/listing/thumb/' . $listing->thumb));
        }

        $listing->thumb = $newThumb;
    }

    $oldPhotos = is_array($listing->photos)
        ? $listing->photos
        : (json_decode($listing->photos, true) ?: []);

    if ($request->has('remove_images')) {
        foreach ($request->remove_images as $removeImage) {
            $oldPhotos = array_filter($oldPhotos, function ($img) use ($removeImage) {
                return $img != $removeImage;
            });

            $filePath = public_path('assets/images/listing/photos/' . $removeImage);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $photo) {
            if (!$photo) {
                continue;
            }

            try {
                $filename = WebpImage::store(
                    $photo,
                    'assets/images/listing/photos',
                    time().'_'.uniqid()
                );
            } catch (\Throwable $e) {
                report($e);
                continue;
            }

            $oldPhotos[] = $filename;
        }
    }

    $businessCategories = $listing->business_categories ?? [];

    if (!empty($subcategoryIds)) {
        $businessCategories = Subcategory::whereIn('id', $subcategoryIds)
            ->pluck('name')
            ->toArray();
    }

    // filled(), not has(): the form posts every field on every save, so a
    // blank one is still "present" and has() hands the column an empty
    // string. MySQL rejects '' for a numeric column outright — an untouched
    // latitude was enough to 500 the whole save. Blank means "leave it
    // alone", which is what the else branch here already says.
    $listing->user_id              = $request->filled('user_id') ? $request->user_id : $listing->user_id;
    $listing->category_id          = $request->filled('category_id') ? $request->category_id : $listing->category_id;
    $listing->location_id          = $request->filled('location_id') ? $request->location_id : $listing->location_id;
    $listing->name                 = $name;
    $listing->established_year     = $request->filled('established_year') ? $request->established_year : $listing->established_year;
    // YMYL credential (registration/license) — optional. Guarded so a save
    // can't fail if the migration adding these columns hasn't run yet.
    if (\Schema::hasColumn('listings', 'registration_label')) {
        $listing->registration_label  = $request->has('registration_label') ? $request->input('registration_label') : $listing->registration_label;
        $listing->registration_number = $request->has('registration_number') ? $request->input('registration_number') : $listing->registration_number;
    }
    $listing->details              = $details;

    if ($request->has('address')) {
        $listing->address = $request->address;
    }

    $listing->features             = $features;
    $listing->faq                  = [
        'question' => $questions,
        'answer'   => $answers,
    ];

    $listing->contact_title         = $contactTitle;
    $listing->contact_person        = $contactPerson;
    $listing->mobile_country_code   = $mobileCountryCode;
    $listing->mobile_numbers        = $mobileNumbers;
    $listing->whatsapp_country_code = $whatsappCountryCode;
    $listing->whatsapp_numbers      = $whatsappNumbers;
    $listing->landline_country_code = $landlineCountryCode;
    $listing->landline_numbers      = $landlineNumbers;
    $listing->extra_emails          = $extraEmails;

    $listing->website              = $website;
    $listing->facebook             = $facebook;
    $listing->instagram            = $instagram;
    $listing->linkedin             = $linkedin;
    $listing->twitter              = $twitter;
    $listing->youtube              = $youtube;

  $listing->business_days = $businessDays;
$listing->open_time = $openTime;
$listing->close_time = $closeTime;

if (Schema::hasColumn('listings', 'open_24_hours')) {
    $listing->open_24_hours = $isOpen24Hours ? 1 : 0;
}

$listing->business_categories = $businessCategories;
    $listing->subcategory_ids       = $subcategoryIds;
    $listing->photos                = array_values(array_unique($oldPhotos));

    if (empty($listing->slug)) {
        $listing->slug = \Illuminate\Support\Str::slug($name ?: 'listing') . '-' . $listing->id;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'meta_title')) {
        $listing->meta_title = $metaTitle;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'meta_keywords')) {
        $listing->meta_keywords = $metaKeywords;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'meta_description')) {
        $listing->meta_description = $metaDescription;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'plot_no')) {
        $listing->plot_no = $plotNo;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'building_name')) {
        $listing->building_name = $buildingName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'street_name')) {
        $listing->street_name = $streetName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'landmark')) {
        $listing->landmark = $landmark;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'area')) {
        $listing->area = $area;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'city')) {
        $listing->city = $city;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'state')) {
        $listing->state = $state;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'pincode')) {
        $listing->pincode = $request->has('pincode') ? $request->pincode : $listing->pincode;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'latitude')) {
        $listing->latitude = $request->filled('latitude') ? $request->latitude : $listing->latitude;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'longitude')) {
        $listing->longitude = $request->filled('longitude') ? $request->longitude : $listing->longitude;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'mobile')) {
        $listing->mobile = $mobileNumbers[0] ?? $listing->mobile;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'email')) {
        $listing->email = $extraEmails[0] ?? $listing->email;
    }

    $listing->save();

    $notify[] = ['success', 'Listing updated successfully'];
    return redirect()->route('admin.listing.index')->withNotify($notify);
}
    protected function validationRule(){
        return [
            'name'=>'required',
            'category'=>'required',
            'location'=>'required',
            'iframe'=>'required',
            'details'=>'required',
            'address'=>'required',
            'features'=>'required|array',
            'title'=>'required|array',
            'content'=>'required|array',
            'images.*'=>'nullable|mimes:jpg,jpeg,png',
        ];
    }

    protected function ability($listing){
        $package = $listing->user->package;
        return [
            'availableFeatures' => $package->features_allow,
            'availableImages' => $package->image_allow,
            'availableContacts' => $package->contact_allow,
            'availableFeatureds' => $package->featured_allow,
            'availableListings' => $package->listing_allow,
        ];
    }

    protected function uploadThumb($thumb,$request){
        if ($request->hasFile('image')) {
            try {
                $imageData = [
                    'file'=>$request->image,
                    'path'=>'assets/images/listing/thumb/',
                    'size'=>'730x484',
                    'prevFile'=>$thumb,
                ];
                $thumb = $this->uploadFile('image',$imageData);
            } catch (Exception $e) {
                $alert = ['danger','Something wrong'];
                return back()->withAlert($alert);
            }
        }
        return $thumb;
    }

    protected function listingToDatabase($request,$thumb,$listing){

        $purifier = new HTMLPurifier();
        $details = $purifier->purify($request->details);


        $listing->name = $request->name;
        $listing->category_id = $request->category;
        $listing->location_id = $request->location;
        $listing->map_iframe = $request->iframe;
        $listing->details = $details;
        $listing->features = $request->features;
        $listing->address = $request->address;
        $listing->contact = [
            'title'=>$request->title,
            'content'=>$request->content,
        ];
        $listing->thumb = $thumb;
        $listing->is_featured = $request->featured;
        $listing->save();
    }

    protected function addExtraImage($request,$listing){
        if ($request->images) {
            foreach ($request->images as $key => $image) {
                try {
                    $imageData = [
                        'file'=>$image,
                        'path'=>'assets/images/listing/extra/',
                        'size'=>'730x484',
                        'prevFile'=>null,
                    ];
                    $image = $this->uploadFile('image',$imageData);
                } catch (Exception $e) {
                    $alert = ['danger','Something wrong'];
                    return back()->withAlert($alert);
                }
                $extraImage = new ExtraImage();
                $extraImage->listing_id = $listing->id;
                $extraImage->image = $image;
                $extraImage->save();
            }
        }
    }

    public function imageRemove($id){
        $extraImage = ExtraImage::findOrFail($id);
        $listing = $extraImage->listing;
        if (!$listing) {
            $alert = ['danger','Sorry! you have no access to remove this image'];
            return back()->withAlert($alert);
        }
        $this->deleteFile('assets/images/listing/extra/'.$extraImage->image);
        $extraImage->delete();
        $alert = ['success','Image removed successfully'];
        return back()->withAlert($alert);
    }

    protected function buildListingQuery(Request $request){
        $query = Listing::with('user','location','category')->orderBy('id','desc');
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($sub) use ($term) {
                $sub->where('name','like',"%{$term}%")
                    ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where('username','like',"%{$term}%")
                            ->orWhere('email','like',"%{$term}%");
                    });
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id',$request->category_id);
        }
        if ($request->filled('location_id')) {
            $query->where('location_id',$request->location_id);
        }
        return $query;
    }

public function updatePending()
{
    $listings = Listing::where(function ($q) {
        $q->where('name_approval_status', 'pending')
          ->orWhere('address_approval_status', 'pending')
          ->orWhere('website_approval_status', 'pending');
    })->latest()->get();

    return view('admin.listing.update-pending', compact('listings'));
}

public function approveNameAddress($id)
{
    $listing = Listing::findOrFail($id);

    if ($listing->name_approval_status === 'pending' && !empty($listing->pending_name)) {
        $listing->name = $listing->pending_name;
        $listing->pending_name = null;
        $listing->name_approval_status = 'approved';
    }

    if ($listing->address_approval_status === 'pending' && !empty($listing->pending_address)) {
        $listing->address = $listing->pending_address;
        $listing->pending_address = null;
        $listing->address_approval_status = 'approved';
    }

    if ($listing->website_approval_status === 'pending' && !empty($listing->pending_website)) {
        $listing->website = $listing->pending_website;
        $listing->pending_website = null;
        $listing->website_approval_status = 'approved';
    }

    $listing->save();

    return back()->withAlert(['success', 'Update request approved successfully']);
}

public function rejectNameAddress($id)
{
    $listing = Listing::findOrFail($id);

    if ($listing->name_approval_status === 'pending') {
        $listing->pending_name = null;
        $listing->name_approval_status = 'rejected';
    }

    if ($listing->address_approval_status === 'pending') {
        $listing->pending_address = null;
        $listing->address_approval_status = 'rejected';
    }

    if ($listing->website_approval_status === 'pending') {
        $listing->pending_website = null;
        $listing->website_approval_status = 'rejected';
    }

    $listing->save();

    return back()->withAlert(['success', 'Update request rejected successfully']);
}


  public function add()
{
    $categories = Category::where('status', 1)->get();
    $locations  = Location::where('status', 1)->get();

    $subcategories = Subcategory::where('status', 1)->get();

    $draftKey = self::addDraftKey();

    return view('admin.listing.add',
        compact('categories','locations','subcategories','draftKey')
    );
}


    public function delete($id)
{
    try {
        $listing = Listing::findOrFail($id);

        // image delete
        if (!empty($listing->image)) {
            $path = public_path('assets/images/listing/' . $listing->image);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $listing->delete();

        $alert = ['success', 'Listing deleted successfully'];
        return back()->withAlert($alert);

    } catch (\Exception $e) {
        $alert = ['danger', 'Delete failed'];
        return back()->withAlert($alert);
    }
}



public function store(Request $request)
{
     \Log::info('LISTING STORE REACHED', [
        'admin_id' => auth()->guard('admin')->id(),
        'ip' => $request->ip(),
    ]);
    
    $rules = [
        // Extra Images — at least 2 required for a new listing.
        'images'                   => 'required|array|min:2',
        'images.*'                 => 'required|image|mimes:jpg,jpeg,png,webp',

        'question'                 => 'nullable|array',
        'question.*'               => 'nullable|string|max:255',

        'answer'                   => 'nullable|array',
        'answer.*'                 => 'nullable|string|max:1000',

        'name'                     => 'required|string|max:255',
        'details'                  => 'nullable|string',

        'meta_title'               => 'nullable|string|max:255',
        'meta_keywords'            => 'nullable|string|max:1000',
        'meta_description'         => 'nullable|string|max:1000',

        'pincode'                  => 'required|string|max:10',
        'established_year'         => 'required|string|max:10',
        'plot_no'                  => 'nullable|string|max:255',
        'building_name'            => 'nullable|string|max:255',
        'street_name'              => 'nullable|string|max:255',
        'landmark'                 => 'nullable|string|max:255',
        'area'                     => 'required|string|max:255',
        'city'                     => 'nullable|string|max:255',
        'state'                    => 'nullable|string|max:255',
        'latitude'                 => 'nullable|numeric',
        'longitude'                => 'nullable|numeric',

        'contact_title'            => 'nullable|string|max:50',
        'contact_person'           => 'required|string|max:255',

        // Numbers themselves stay nullable here — "at least one of the three
        // groups", "mobile blocks landline", and "must be a real Indian
        // number" are cross-field rules, handled below via ->after() where
        // every value can be seen at once (a per-field rule sees only that
        // one field, e.g. mobile_numbers.0 can't know what landline_numbers
        // holds).
        'mobile_country_code'      => 'nullable|array',
        'mobile_country_code.*'    => 'nullable|string|max:10',
        'mobile_numbers'           => 'nullable|array',
        'mobile_numbers.*'         => 'nullable|string|max:20',

        'whatsapp_country_code'    => 'nullable|array',
        'whatsapp_country_code.*'  => 'nullable|string|max:10',
        'whatsapp_numbers'         => 'nullable|array',
        'whatsapp_numbers.*'       => 'nullable|string|max:20',

        'landline_country_code'    => 'nullable|array',
        'landline_country_code.*'  => 'nullable|string|max:10',
        'landline_numbers'         => 'nullable|array',
        'landline_numbers.*'       => 'nullable|string|max:20',

        // Email — at least one required (checked in ->after() below since
        // "at least one populated slot in the array" isn't a per-item rule).
        'extra_emails'             => 'required|array',
        'extra_emails.*'           => 'nullable|email|max:255',

        // Business Timings — at least one day, or Open 24 Hours, checked
        // below (a plain 'required' here would reject "Open 24 Hours" with
        // no day ticked, which is a valid answer).
        'business_days'            => 'nullable|array',
        'business_days.*'          => 'nullable|string|max:20',
        'open_24_hours'            => 'nullable|string',

        'open_time'                => 'nullable|array',
        'open_time.*'              => 'nullable|string|max:50',

        'close_time'               => 'nullable|array',
        'close_time.*'             => 'nullable|string|max:50',

        'location_id'              => 'required|exists:locations,id',
        'user_id'                  => 'nullable|exists:users,id',
        // listings.category_id is NOT NULL and every one of the 10,515 rows
        // has a real category, so 'nullable' only meant an empty pick reached
        // MySQL as '' and failed the save with a 500 instead of telling the
        // admin which field was missing.
        'category_id'              => 'required|exists:categories,id',
        'subcategory_ids'          => 'required|array|min:1',
        'subcategory_ids.*'        => 'nullable|exists:subcategories,id',

        // Business Image — required for a new listing.
        'thumb'                    => 'required|image|mimes:jpg,jpeg,png,webp',
    ];

    $validator = Validator::make($request->all(), $rules);

    $validator->after(function ($validator) use ($request) {
        $mobiles   = array_values(array_filter((array) $request->input('mobile_numbers', []), fn ($v) => filled($v)));
        $whatsapps = array_values(array_filter((array) $request->input('whatsapp_numbers', []), fn ($v) => filled($v)));
        $landlines = array_values(array_filter((array) $request->input('landline_numbers', []), fn ($v) => filled($v)));

        // At least one of Mobile / WhatsApp / Landline is required.
        if (!$mobiles && !$whatsapps && !$landlines) {
            $validator->errors()->add(
                'mobile_numbers',
                'Please provide at least one contact number — Mobile, WhatsApp or Landline.'
            );
        }

        // A Mobile number blocks Landline — they can't both be filled.
        if ($mobiles && $landlines) {
            $validator->errors()->add(
                'landline_numbers',
                'Landline Number cannot be added when a Mobile Number is already set. Remove one to continue.'
            );
        }

        // Each provided number is checked against the shape a real Indian
        // number can take, same as the app's own signup/enquiry forms do.
        $indianMobile = new \App\Rules\IndianMobile();
        $indianLandline = new \App\Rules\IndianLandline();

        foreach ($mobiles as $i => $number) {
            $indianMobile->validate("mobile_numbers.$i", $number, fn ($message) => $validator->errors()->add('mobile_numbers', "Mobile Number: $message"));
        }

        foreach ($whatsapps as $i => $number) {
            $indianMobile->validate("whatsapp_numbers.$i", $number, fn ($message) => $validator->errors()->add('whatsapp_numbers', "WhatsApp Number: $message"));
        }

        foreach ($landlines as $i => $number) {
            $indianLandline->validate("landline_numbers.$i", $number, fn ($message) => $validator->errors()->add('landline_numbers', $message));
        }

        // Email — at least one non-empty address in the array.
        $emails = array_values(array_filter((array) $request->input('extra_emails', []), fn ($v) => filled($v)));
        if (!$emails) {
            $validator->errors()->add('extra_emails', 'Please provide at least one email address.');
        }

        // Business Timings — either specific days are picked, or the
        // business is marked Open 24 Hours.
        $days = array_values(array_filter((array) $request->input('business_days', []), fn ($v) => filled($v)));
        if (!$days && !$request->filled('open_24_hours')) {
            $validator->errors()->add('business_days', 'Please select Business Timings — pick at least one day or mark Open 24 Hours.');
        }
    });

    $validator->validate();
    $lockKey = 'listing_submit_' . auth()->guard('admin')->id();

if (!cache()->add($lockKey, true, 15)) {
    $notify[] = ['error', 'Listing already submitting, please wait'];
    return back()->withNotify($notify);
}


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    $blockedWords = [
        'sex', 'xxx', 'porn', 'adult', 'nude', 'call girl', 'callgirl',
        'escort', 'escorts', 'hot video', 'blue film', 'bluefilm',
        'sex video', 'sexvideo', 'porn video', 'pornvideo', 'sexy',
        'night service', 'prostitute'
    ];

    $cleanText = function ($text) use ($blockedWords) {
        if (is_null($text)) {
            return null;
        }

        $text = (string) $text;

        $text = preg_replace('/https?:\/\/\S+/i', '', $text);
        $text = preg_replace('/www\.\S+/i', '', $text);
        $text = preg_replace('/ftp:\/\/\S+/i', '', $text);
        $text = preg_replace('/mailto:\S+/i', '', $text);
        $text = preg_replace('/tel:\S+/i', '', $text);

        $text = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $text);
        $text = preg_replace('/\s(?:href|src)\s*=\s*("|\').*?\1/i', '', $text);

        foreach ($blockedWords as $word) {
            $pattern = '/' . preg_quote($word, '/') . '/i';
            $text = preg_replace($pattern, '', $text);
        }

        $text = preg_replace('/\s{2,}/', ' ', $text);
        return trim($text);
    };

    $cleanArray = function ($items) use ($cleanText) {
        $items = is_array($items) ? $items : [];
        $cleaned = [];

        foreach ($items as $item) {
            $value = $cleanText($item);
            if (!is_null($value) && $value !== '') {
                $cleaned[] = $value;
            }
        }

        return array_values($cleaned);
    };

    $cleanSimpleArray = function ($items) {
        $items = is_array($items) ? $items : [];

        return array_values(array_filter(array_map(function ($item) {
            return is_string($item) ? trim($item) : $item;
        }, $items), function ($item) {
            return $item !== null && $item !== '';
        }));
    };

    /*
    |--------------------------------------------------------------------------
    | Clean fields
    |--------------------------------------------------------------------------
    */
  // Whatever the admin typed is the business's name. Lower-casing it and
  // title-casing it back turned "NBF Public School" into "Nbf Public School",
  // and would do the same to every acronym a business puts in its name — CA,
  // IT, KFC. No rule can tell an acronym from an ordinary word, and update()
  // has always stored the name as entered, so add() now does the same.
  $name = $cleanText($request->name);
    // See the equivalent note in update() — HTMLPurifier is the
    // authoritative HTML-safety pass since $listing->details is rendered
    // unescaped on the public listing page.
    $details             = (new HTMLPurifier())->purify((string) $cleanText($request->details));
    $metaTitle           = $cleanText($request->meta_title);
    $metaKeywords        = $cleanText($request->meta_keywords);
    $metaDescription     = $cleanText($request->meta_description);

    // Backfill anything left blank — see fallbackSeoFor()'s docblock.
    [$metaTitle, $metaKeywords, $metaDescription, $details] = $this->fallbackSeoFor(
        $name, $request->city, $request->category_id, $metaTitle, $metaKeywords, $metaDescription, $details
    );

    $plotNo              = $cleanText($request->plot_no);
    $buildingName        = $cleanText($request->building_name);
    $streetName          = $cleanText($request->street_name);
    $landmark            = $cleanText($request->landmark);
    $area                = $cleanText($request->area);
    $city                = $cleanText($request->city);
    $state               = $cleanText($request->state);
    $contactTitle        = $cleanText($request->contact_title);
    $contactPerson       = $cleanText($request->contact_person);
    $website   = trim($request->website);
$facebook  = trim($request->facebook);
$instagram = trim($request->instagram);
$linkedin  = trim($request->linkedin);
$twitter   = trim($request->twitter);
$youtube   = trim($request->youtube);
    $features            = $cleanArray($request->features);
    $questions           = $cleanArray($request->question);
    $answers             = $cleanArray($request->answer);

    $mobileCountryCode   = $cleanSimpleArray($request->mobile_country_code);
    $mobileNumbers       = $cleanSimpleArray($request->mobile_numbers);
    $whatsappCountryCode = $cleanSimpleArray($request->whatsapp_country_code);
    $whatsappNumbers     = $cleanSimpleArray($request->whatsapp_numbers);
    $landlineCountryCode = $cleanSimpleArray($request->landline_country_code);
    $landlineNumbers     = $cleanSimpleArray($request->landline_numbers);
    $extraEmails         = $cleanSimpleArray($request->extra_emails);
   // See the matching comment in store() — timing_days[] is the per-row,
   // duplicate-allowed array; business_days[] (checkboxes) is the fallback.
   $businessDays = $request->has('timing_days')
       ? $cleanSimpleArray($request->timing_days)
       : $cleanSimpleArray($request->business_days);

$isOpen24Hours = $request->boolean('open_24_hours');

if ($isOpen24Hours) {

    $openTime = ['12:00 AM'];
    $closeTime = ['11:59 PM'];

} else {

    $openTime = $cleanSimpleArray($request->open_time);
    $closeTime = $cleanSimpleArray($request->close_time);
}
    $subcategoryIds      = array_values(array_filter($request->subcategory_ids ?? []));

    /*
    |--------------------------------------------------------------------------
    | Thumb upload
    |--------------------------------------------------------------------------
    */
    $thumb = null;
    if ($request->hasFile('thumb')) {
        $file = $request->file('thumb');

        try {
            $thumb = WebpImage::store(
                $file,
                'assets/images/listing/thumb',
                time().'_'.uniqid()
            );
        } catch (\Throwable $e) {
            report($e);
            cache()->forget($lockKey);

            return back()
                ->withErrors(['thumb' => 'Failed to upload the business image. Please try again.'])
                ->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Extra images upload -> store in photos column
    |--------------------------------------------------------------------------
    */
    $uploadedPhotos = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $photo) {
            if (!$photo) {
                continue;
            }

            try {
                $filename = WebpImage::store(
                    $photo,
                    'assets/images/listing/photos',
                    time().'_'.uniqid()
                );
            } catch (\Throwable $e) {
                report($e);
                continue;
            }

            $uploadedPhotos[] = $filename;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Business categories
    |--------------------------------------------------------------------------
    */
    $businessCategories = [];
    if (!empty($subcategoryIds)) {
        $businessCategories = Subcategory::whereIn('id', $subcategoryIds)
            ->pluck('name')
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Address
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Save listing
    |--------------------------------------------------------------------------
    */
    $listing = new Listing();

    // user_id is NOT NULL DEFAULT 0 in the schema, but the form's user picker
    // is optional (validated `nullable`) — an admin-created listing need not
    // belong to a registered user. Fall back to the column default instead of
    // handing MySQL a null and failing the whole save.
    $listing->user_id              = $request->user_id ?: 0;
    $listing->created_by_admin_id = auth()->guard('admin')->id();
    $listing->category_id          = $request->category_id;
    // Same trap the edit form hit: the form posts these whether or not they
    // were filled in, so an untouched field arrives as '' — and MySQL rejects
    // '' for an integer column, failing the whole save with a 500. Both are
    // nullable in the schema, so an empty one is stored as NULL.
    $listing->location_id          = $request->filled('location_id') ? $request->location_id : null;
    $listing->name                 = $name;
    $listing->established_year     = $request->filled('established_year') ? $request->established_year : null;
    $listing->slug                 = Str::slug($name ?: 'listing') . '-' . time();
    $listing->details              = $details;
  
    $listing->features             = $features;
   
    $listing->thumb                = $thumb;
    // is_featured is NOT NULL; ?? only catches a missing value, not an empty
    // string, so filled() is what actually guards the column here.
    $listing->is_featured          = $request->filled('featured') ? $request->featured : 0;
    // Listings created from the admin panel are trusted content (the
    // admin themselves entered it) and should go live immediately
    // instead of sitting in the same pending-approval queue used for
    // public/user-submitted listings. 1 = approved (see index()/
    // approved()/pending() above, which filter on this same status
    // column: 0 = pending, 1 = approved, 2 = rejected).
    $listing->status               = 1;

    $listing->faq = [
        'question' => $questions,
        'answer'   => $answers,
    ];

    $listing->contact_title         = $contactTitle;
    $listing->contact_person        = $contactPerson;
    $listing->mobile_country_code   = $mobileCountryCode;
    $listing->mobile_numbers        = $mobileNumbers;
    $listing->whatsapp_country_code = $whatsappCountryCode;
    $listing->whatsapp_numbers      = $whatsappNumbers;
    $listing->landline_country_code = $landlineCountryCode;
    $listing->landline_numbers      = $landlineNumbers;
    $listing->extra_emails          = $extraEmails;
    $listing->website   = $website;
    $listing->facebook  = $facebook;
$listing->instagram = $instagram;
$listing->linkedin  = $linkedin;
$listing->twitter   = $twitter;
$listing->youtube   = $youtube;
    $listing->business_days         = $businessDays;
    $listing->open_time             = $openTime;
    $listing->close_time            = $closeTime;

    if (Schema::hasColumn('listings', 'open_24_hours')) {
    $listing->open_24_hours = $isOpen24Hours ? 1 : 0;
}
    $listing->business_categories   = $businessCategories;
    $listing->subcategory_ids       = $subcategoryIds;

    // photos column me extra images save hongi
    $listing->photos = $uploadedPhotos;

    if (Schema::hasColumn('listings', 'meta_title')) {
        $listing->meta_title = $metaTitle;
    }

    if (Schema::hasColumn('listings', 'meta_keywords')) {
        $listing->meta_keywords = $metaKeywords;
    }

    if (Schema::hasColumn('listings', 'meta_description')) {
        $listing->meta_description = $metaDescription;
    }

    if (Schema::hasColumn('listings', 'plot_no')) {
        $listing->plot_no = $plotNo;
    }

    if (Schema::hasColumn('listings', 'building_name')) {
        $listing->building_name = $buildingName;
    }

    if (Schema::hasColumn('listings', 'street_name')) {
        $listing->street_name = $streetName;
    }

    if (Schema::hasColumn('listings', 'landmark')) {
        $listing->landmark = $landmark;
    }

    if (Schema::hasColumn('listings', 'mobile')) {
        $listing->mobile = $request->mobile;
    }

    if (Schema::hasColumn('listings', 'email')) {
        $listing->email = $request->email;
    }

    if (Schema::hasColumn('listings', 'area')) {
        $listing->area = $area;
    }

    if (Schema::hasColumn('listings', 'city')) {
        $listing->city = $city;
    }

    if (Schema::hasColumn('listings', 'state')) {
        $listing->state = $state;
    }

    if (Schema::hasColumn('listings', 'pincode')) {
        $listing->pincode = $request->pincode;
    }

    if (Schema::hasColumn('listings', 'latitude')) {
    $listing->latitude = $request->filled('latitude') ? (float) $request->latitude : null;
}

if (Schema::hasColumn('listings', 'longitude')) {
    $listing->longitude = $request->filled('longitude') ? (float) $request->longitude : null;
}

    $listing->verified = 0;
    $listing->trust    = 0;

    $listing->save();
 cache()->forget($lockKey);
    $notify[] = ['success', 'Listing added successfully'];

    // The listing is in; the browser can throw its autosaved copy away.
    return redirect()->route('admin.listing.index')
        ->with('clear_form_draft', self::addDraftKey())
        ->withNotify($notify);
}




   public function aiGenerate(Request $request)
    {
        // config(), not env(): env() only resolves while config/ is being
        // built, so once `config:cache` has run — a routine production step —
        // this read returns null and every generation fails with a message
        // blaming a .env that is in fact correct. callGemini() already reads
        // the same key this way.
        $apiKey = config('services.gemini.key');

        if (empty($apiKey)) {
            return response()->json([
                'status' => false,
                'message' => 'GEMINI_API_KEY missing in .env'
            ], 500);
        }

        $name = trim($request->name ?? '');
        $categoryName = trim($request->category_name ?? '');
        $area = trim($request->area ?? '');
        $city = trim($request->city ?? '');
        $state = trim($request->state ?? '');
        $subcategories = $request->subcategories ?? [];

        if (!is_array($subcategories)) {
            $subcategories = [];
        }

        $subcategories = array_filter(array_map('trim', $subcategories));

        $schema = [
            'type' => 'object',
            'properties' => [
                'meta_title' => [
                    'type' => 'string',
                ],
                'meta_keywords' => [
                    'type' => 'string',
                ],
                'meta_description' => [
                    'type' => 'string',
                ],
                'details' => [
                    'type' => 'string',
                ],
                'faqs' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => ['type' => 'string'],
                            'answer' => ['type' => 'string'],
                        ],
                        'required' => ['question', 'answer'],
                    ],
                ],
            ],
            'required' => [
                'meta_title',
                'meta_keywords',
                'meta_description',
                'details',
                'faqs'
            ],
        ];

        $basePrompt = "Generate unique business SEO content in valid JSON only.

Business Data:
Name: {$name}
Category: {$categoryName}
Area: {$area}
City: {$city}
State: {$state}
Subcategories: " . implode(', ', $subcategories) . "

Strict Rules (ABSOLUTE - NO EXCEPTIONS):
- Return JSON only, no markdown, no code blocks
- meta_title MUST be 60 CHARACTERS OR LESS (count every character including spaces and punctuation)
- meta_title format: \"[Full Business Name] in [Area], [City]\"
- STEP 1: Start with the FULL business name as provided
- STEP 2: Add \" in [Area], [City]\" (use the Area field provided, NOT just City)
- STEP 3: Count total characters. If under 60, use it. If over 60, THEN shorten the business name.
- IMPORTANT: Area name in the title is CRITICAL for local SEO - never omit it
- Examples (all under 60 chars):
  * \"Balaji Packers and Movers in Vaishali Nagar, Jaipur\" (52 chars) ✓
  * \"Ashu Pest Control in Model Town, Indore\" (40 chars) ✓
  * \"Sharma Plumber Services in Sector 5, Mumbai\" (44 chars) ✓
- Only abbreviate business name if result exceeds 60 chars (e.g., \"ABC Packers & Movers\" instead of \"ABC Packers and Movers\" to save 4 chars)
- CRITICAL: Always include [Area], [City] - never use just [City] alone. Full Area name is essential.
- Verify character count CAREFULLY. If unsure about length, count manually. Never exceed 60.
- meta_description MUST be 160 CHARACTERS OR LESS (count every character)
- meta_description should be 145-160 chars, not less, not more
- meta_keywords: 5-8 comma-separated keywords, no special characters except commas
- Include area-specific, location-based and service-related keywords
- Example keywords: \"packers movers, relocation, Vaishali Nagar shifting, Jaipur moving, household transport\"
- details: minimum 300 words, proper HTML <p> paragraphs, specific to business
- no placeholder text, no generic content, no links, no fake claims
- faqs: exactly 3 items with complete questions and answers
- write natural, engaging, SEO-friendly business content";

        $result = $this->callGemini($apiKey, $basePrompt, $schema);

        if (!$result['status']) {
            return response()->json($result, 500);
        }

        $data = $result['data'];

        $validation = $this->validateAiData($data);
        $seriousErrors = $this->seriousAiErrors($validation['errors']);

        // Only a fault the response cannot be repaired from is worth a second
        // round trip. An over-long meta_title is not one: limitText() trims it
        // a few lines below, so asking the model to shorten it doubled the
        // request for a result the code was going to produce anyway — and
        // overshooting those two limits is the thing gpt-4o-mini does most.
        if (!empty($seriousErrors)) {
            $retryPrompt = $basePrompt . "

The previous output was invalid. Fix these issues STRICTLY and exactly:
- " . implode("\n- ", $seriousErrors) . "

CRITICAL: Character limits are ABSOLUTE:
- meta_title MUST be 60 characters or LESS (count carefully)
- meta_description MUST be 160 characters or LESS (count carefully)
- If unsure, make it shorter to be safe

Return corrected JSON only.
Do not explain anything.
Do not add markdown.
Count characters carefully before responding.";

            $retryResult = $this->callGemini($apiKey, $retryPrompt, $schema);

            if ($retryResult['status']) {
                $data = $retryResult['data'];
                $seriousErrors = $this->seriousAiErrors(
                    $this->validateAiData($data)['errors']
                );
            }
        }

        if (!empty($seriousErrors)) {
            return response()->json([
                'status' => false,
                'message' => 'AI returned invalid structured content after retry',
                'errors' => $seriousErrors,
                'data' => $data
            ], 422);
        }

        $metaTitle = $this->limitText($data['meta_title'] ?? '', 60);
        $metaKeywords = trim(strip_tags($data['meta_keywords'] ?? ''));
        $metaDescription = $this->limitText($data['meta_description'] ?? '', 160);
        $details = $this->cleanHtmlContent($data['details'] ?? '');
        $faqs = $this->normalizeFaqs($data['faqs'] ?? []);

        return response()->json([
            'status' => true,
            'data' => [
                'meta_title' => $metaTitle,
                'meta_keywords' => $metaKeywords,
                'meta_description' => $metaDescription,
                'details' => $details,
                'faqs' => $faqs,
            ]
        ]);
    }

/**
 * Spelling/grammar pass over whatever listing-form fields the browser sends.
 *
 * The correction itself lives in App\Services\SpellFixer so this and the
 * bulk `listings:fix-spelling` command share one prompt and one set of
 * safety guards. Returns only the fields that actually changed, so the front
 * end can apply the fix and show an undo.
 */
public function spellFix(Request $request, \App\Services\SpellFixer $fixer)
{
    $fields = $request->input("fields", []);

    if (!is_array($fields) || $fields === []) {
        return response()->json(["status" => false, "message" => "No fields sent"], 422);
    }

    $result = $fixer->correct($fields);

    if (!$result["status"]) {
        return response()->json([
            "status"  => false,
            "message" => $result["message"] ?? "Spell check failed",
        ], 502);
    }

    return response()->json([
        "status"      => true,
        "corrections" => $result["corrections"],
    ]);
}

   /**
    * Whoever submits a listing — admin, agent, or a business owner via the
    * public free-listing / self-service form — can leave meta_title,
    * meta_keywords, meta_description and details blank, and both store()
    * and update() have always saved that as-is. That is exactly the class
    * of listing the SEO audit kept turning up (missing title/description/
    * keywords/content, sometimes hundreds at a time). Rather than relying
    * on someone noticing it later, fill in a sane deterministic default at
    * save time for whichever of the four fields is empty — the same recipe
    * used to backfill the existing rows during the SEO cleanup.
    *
    * @return array{0: string, 1: string, 2: string, 3: string}  [metaTitle, metaKeywords, metaDescription, details]
    */
   private function fallbackSeoFor(
       ?string $name,
       ?string $city,
       ?int $categoryId,
       ?string $metaTitle,
       ?string $metaKeywords,
       ?string $metaDescription,
       ?string $details
   ): array {
       $name     = trim((string) $name);
       $city     = trim((string) $city);
       $category = $categoryId ? (string) optional(Category::find($categoryId))->name : '';
       $category = $category !== '' ? $category : 'Service';
       // Strip a trailing "Services" so it doesn't double up with the word
       // this method appends itself (many category names already end in it).
       $categoryBase = trim((string) preg_replace('/\s+services?$/i', '', $category)) ?: $category;

       if ($name === '') {
           // Nothing sane to build a fallback from — leave the fields as
           // they came in rather than write "Service in City" everywhere.
           return [(string) $metaTitle, (string) $metaKeywords, (string) $metaDescription, (string) $details];
       }

       if (trim((string) $metaTitle) === '') {
           $candidates = array_values(array_filter([
               $city !== '' ? "{$name} - {$category} in {$city}" : null,
               "{$name} - {$category}",
               "Best {$categoryBase} - {$name}" . ($city !== '' ? " in {$city}" : ''),
               "{$name} | ServiceBazzar",
           ]));

           $metaTitle = null;
           foreach ($candidates as $candidate) {
               $len = mb_strlen($candidate);
               if ($len >= 30 && $len <= 60) { $metaTitle = $candidate; break; }
           }
           // Nothing landed in range (an unusually short name/category) —
           // take the longest candidate and pad or trim it to fit rather
           // than ship a title under Google's ~30-char floor.
           if ($metaTitle === null) {
               usort($candidates, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
               $metaTitle = $candidates[0] ?? $name;
               if (mb_strlen($metaTitle) > 60) $metaTitle = mb_substr($metaTitle, 0, 60);
               // An unusually short name/category combo (rare) may still
               // land under 30 chars here — a real title beats none.
           }
       }

       if (trim((string) $metaKeywords) === '') {
           $metaKeywords = collect([
               mb_strtolower($name),
               mb_strtolower($name) . ' near me',
               'best ' . mb_strtolower($name),
               mb_strtolower($category),
               $city !== '' ? mb_strtolower($category) . ' in ' . mb_strtolower($city) : null,
           ])->filter()->unique()->implode(', ');
       }

       if (trim((string) $metaDescription) === '') {
           $sentence = $city !== ''
               ? "{$name} is a trusted {$category} provider in {$city}. Compare verified providers, check ratings and book online."
               : "{$name} is a trusted {$category} provider. Compare verified providers, check ratings and book online.";
           $metaDescription = mb_strlen($sentence) <= 160 ? $sentence : mb_substr($sentence, 0, 160);
       }

       if (trim(strip_tags((string) $details)) === '') {
           $cityPhrase = $city !== '' ? " in {$city}" : '';
           $details = "<p><strong>{$name}</strong> offers professional {$categoryBase} services{$cityPhrase}. "
               . "This is a verified listing on ServiceBazzar — contact the provider directly to check pricing, "
               . "availability, and book a service that fits your schedule.</p>";
       }

       return [$metaTitle, $metaKeywords, $metaDescription, $details];
   }

   private function callGemini($apiKey, $prompt, $schema)
{
    $apiKey = config('services.gemini.key');

    if (!$apiKey) {
        return [
            'status' => false,
            'message' => 'GEMINI_API_KEY missing in .env',
        ];
    }

    /*
     * Hand the MySQL connection back before waiting on OpenAI.
     *
     * This request runs exactly one query — the admin lookup — and then sits
     * on the connection for the 6-24 seconds the model takes to answer.
     * MySQL allows far fewer concurrent connections than PHP has workers, so
     * a few admins generating content at once can spend the whole allowance
     * on requests that are not using the database at all. That is how
     * `[1203] max_user_connections` and the 504s that follow start.
     *
     * Laravel reconnects lazily on the next query, so nothing below needs to
     * know this happened; the cost is one handshake against 20-odd seconds
     * of a connection nobody was using.
     */
    DB::disconnect();

    // 429/503/408 from OpenAI are a bad minute at their end, not a bad
    // request — backed off and repeated rather than shown to the admin.
    $attempt = HttpRetry::send(function () use ($apiKey, $prompt) {
        return Http::withHeaders([
                'Authorization' => 'Bearer ' . trim($apiKey),
                'Content-Type'  => 'application/json',
            ])
            ->acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    // Asking in the system role rather than burying it in the
                    // prompt: without it the model regularly answered with a
                    // ```json fence, which json_decode() rejects and which
                    // used to cost a whole second round trip to undo.
                    [
                        'role' => 'system',
                        'content' => 'You reply with a single valid JSON object and nothing else. No markdown, no code fences, no commentary.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                // Enforced by the API, not merely requested — the same
                // setting AiContentGenerator::payload() has always used.
                'response_format' => ['type' => 'json_object'],
                // The prompt asks for ~300 words plus three FAQs, which lands
                // near 900 tokens. The ceiling only stops a model that decides
                // to keep going; it is not a target.
                'max_tokens' => 2000,
            ]);
    // 25s budget against a 30s timeout: a provider that refuses in a second is
    // worth another go, one that burns the whole timeout is not — a second
    // minute here is exactly how the browser ends up seeing a 408.
    }, 2, 800, 'openai.chat', 25);

    $response = $attempt['response'];

    if (!$response) {
        Log::error('AI API unreachable', [
            'attempts' => $attempt['attempts'],
            'error'    => optional($attempt['exception'])->getMessage(),
        ]);

        return [
            'status' => false,
            'message' => 'AI service abhi busy hai. Thodi der baad dobara try karein.',
        ];
    }

    if (!$response->successful()) {
        Log::error('AI API error', [
            'status'   => $response->status(),
            'attempts' => $attempt['attempts'],
            'body'     => $response->body(),
        ]);

        return [
            'status' => false,
            'message' => HttpRetry::retryable($response)
                ? 'AI service abhi busy hai. Thodi der baad dobara try karein.'
                : 'AI request reject ho gayi. Content thoda chhota karke try karein.',
            'error' => $response->json() ?: $response->body(),
        ];
    }

    $content = $response->json('choices.0.message.content');

    Log::info('AI raw content', ['content' => $content]);

    if (empty($content)) {
        return [
            'status' => false,
            'message' => 'Empty response from AI',
            'raw_response' => $response->json(),
        ];
    }

    $data = json_decode($content, true);

    if (!is_array($data)) {
        $cleanContent = trim($content);
        $cleanContent = preg_replace('/^```json\s*|^```\s*|```$/m', '', $cleanContent);
        $cleanContent = trim($cleanContent);

        $data = json_decode($cleanContent, true);
    }

    if (!is_array($data)) {
        return [
            'status' => false,
            'message' => 'Model did not return valid JSON',
            'raw_content' => $content,
        ];
    }

    return [
        'status' => true,
        'data' => $data
    ];
}

    /**
     * The validation errors that actually need the model's help.
     *
     * The two length overruns are dropped because limitText() truncates both
     * fields on the way out — they describe a response this controller can
     * finish repairing on its own.
     */
    private function seriousAiErrors(array $errors): array
    {
        // Character limit overages are handled by limitText() truncation,
        // so they're not serious enough to warrant an expensive retry.
        // Only structural/content errors (empty fields, missing FAQs) trigger retry.
        return array_values(array_filter($errors, function ($error) {
            return !in_array($error, [
                'meta_title exceeds 60 characters',
                'meta_description exceeds 160 characters',
            ], true);
        }));
    }

    private function validateAiData(array $data)
    {
        $errors = [];

        $metaTitle = trim(strip_tags($data['meta_title'] ?? ''));
        $metaDescription = trim(strip_tags($data['meta_description'] ?? ''));
        $details = trim(strip_tags($data['details'] ?? ''));
        $faqs = $data['faqs'] ?? [];

        if ($metaTitle === '') {
            $errors[] = 'meta_title is empty';
        }

        if (mb_strlen($metaTitle) > 60) {
            $errors[] = 'meta_title exceeds 60 characters';
        }

        if ($metaDescription === '') {
            $errors[] = 'meta_description is empty';
        }

        if (mb_strlen($metaDescription) > 160) {
            $errors[] = 'meta_description exceeds 160 characters';
        }

        if ($details === '') {
            $errors[] = 'details is empty';
        }

        if (str_word_count($details) < 180) {
    $errors[] = 'details is under 180 words';
}

        if (!is_array($faqs) || count($faqs) < 3) {
            $errors[] = 'faqs must contain at least 3 items';
        } else {
            foreach ($faqs as $index => $faq) {
                if (empty($faq['question']) || empty($faq['answer'])) {
                    $errors[] = 'faq item ' . ($index + 1) . ' is incomplete';
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function limitText($text, $limit)
    {
        $text = trim(strip_tags($text));

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        // Hard cap at limit (don't break at word boundary if it means exceeding)
        $trimmed = mb_substr($text, 0, $limit);

        // Try to break at last space, but only if it keeps us under limit
        $lastSpace = mb_strrpos($trimmed, ' ');
        if ($lastSpace !== false && $lastSpace > ($limit * 0.7)) {
            // Only use word boundary if it's substantial (at least 70% of limit)
            $trimmed = mb_substr($trimmed, 0, $lastSpace);
        }

        return rtrim($trimmed, " ,.-");
    }

    private function cleanHtmlContent($html)
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', '', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = trim($html);

        return $html;
    }


public function usedSubcategories(Request $request)
{
    $validated = $request->validate([
        'location_id' => ['required', 'integer', 'exists:locations,id'],
        'category_id' => ['required', 'integer', 'exists:categories,id'],
    ]);

    $usedSubcategoryIds = Listing::query()
        ->where('status', 1)
        ->where('location_id', $validated['location_id'])
        ->where('category_id', $validated['category_id'])
        ->whereNotNull('subcategory_ids')
        ->pluck('subcategory_ids')
        ->flatMap(function ($ids) {
            if (is_array($ids)) {
                return $ids;
            }

            $decoded = json_decode($ids, true);

            return is_array($decoded) ? $decoded : [];
        })
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->unique()
        ->values();

    $subcategories = Subcategory::query()
        ->where('status', 1)
        ->where('category_id', $validated['category_id'])
        ->whereIn('id', $usedSubcategoryIds->all())
        ->orderBy('name')
        ->get([
            'id',
            'category_id',
            'name',
        ]);

    return response()->json([
        'status' => true,
        'data' => $subcategories,
    ]);
}

    private function normalizeFaqs($faqs)
    {
        if (!is_array($faqs)) {
            return [];
        }

        $normalized = [];

        foreach ($faqs as $faq) {
            if (!is_array($faq)) {
                continue;
            }

            $question = trim(strip_tags($faq['question'] ?? ''));
            $answer = trim(strip_tags($faq['answer'] ?? ''));

            if ($question !== '' && $answer !== '') {
                $normalized[] = [
                    'question' => $question,
                    'answer' => $answer,
                ];
            }
        }

        return array_slice($normalized, 0, 3);
    }


    public function sendWhatsApp(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Manual Request Validation
    |--------------------------------------------------------------------------
    */

    $phoneInput  = trim((string) $request->input('phone'));
    $listingName = trim((string) $request->input('listing_name'));
    $listingUrl  = trim((string) $request->input('listing_url'));

    if ($phoneInput === '') {
        return response()->json([
            'success' => false,
            'message' => 'WhatsApp number required hai.',
        ], 422);
    }

    if ($listingName === '') {
        return response()->json([
            'success' => false,
            'message' => 'Listing name required hai.',
        ], 422);
    }

    if (mb_strlen($listingName) > 255) {
        return response()->json([
            'success' => false,
            'message' => 'Listing name 255 characters se zyada nahi hona chahiye.',
        ], 422);
    }

    if (
        $listingUrl === '' ||
        !filter_var($listingUrl, FILTER_VALIDATE_URL)
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Valid listing URL required hai.',
        ], 422);
    }

    if (strlen($listingUrl) > 1000) {
        return response()->json([
            'success' => false,
            'message' => 'Listing URL bahut lamba hai.',
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Clean Destination Number
    |--------------------------------------------------------------------------
    */

    $phone = preg_replace('/\D+/', '', $phoneInput);

    if (strlen($phone) < 10 || strlen($phone) > 15) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid WhatsApp number. Country code ke saath number bhejiye.',
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Gupshup Configuration
    |--------------------------------------------------------------------------
    */

    $apiKey  = config('services.gupshup.api_key');
    $appName = config('services.gupshup.app_name');

    $source = preg_replace(
        '/\D+/',
        '',
        (string) config('services.gupshup.source')
    );

    $templateId = config('services.gupshup.template_id');

    if (!$apiKey || !$appName || !$source || !$templateId) {
        Log::error('Gupshup configuration missing', [
            'api_key_exists'     => !empty($apiKey),
            'app_name_exists'    => !empty($appName),
            'source_exists'      => !empty($source),
            'template_id_exists' => !empty($templateId),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gupshup configuration incomplete hai. .env values check karein.',
        ], 500);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Dynamic Button URL Suffix
    |--------------------------------------------------------------------------
    */

    $listingPath = parse_url($listingUrl, PHP_URL_PATH);
    $listingPath = trim((string) $listingPath, '/');

    if (!str_starts_with(strtolower($listingPath), 'listings/')) {
        return response()->json([
            'success' => false,
            'message' => 'Listing URL valid format mein nahi hai.',
        ], 422);
    }

    $buttonSuffix = preg_replace(
        '#^listings/#i',
        '',
        $listingPath
    );

    $buttonSuffix = trim((string) $buttonSuffix, '/');

    if ($buttonSuffix === '') {
        return response()->json([
            'success' => false,
            'message' => 'Listing URL ka dynamic button value generate nahi hua.',
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Gupshup Template Payload
    |--------------------------------------------------------------------------
    */

    $template = [
        'id' => $templateId,
        'params' => [
            $listingName,
            $buttonSuffix,
        ],
    ];

    try {
        // Gupshup answers 408/429/503 whenever it is under load. Those are
        // repeated here instead of being handed to the browser, which used to
        // show the admin a bare "503" for something that worked on the next try.
        $attempt = HttpRetry::send(function () use ($apiKey, $source, $phone, $appName, $template) {
            return Http::asForm()
                ->acceptJson()
                ->withHeaders([
                    'apikey'        => $apiKey,
                    'Cache-Control' => 'no-cache',
                ])
                ->connectTimeout(10)
                ->timeout(30)
                ->post('https://api.gupshup.io/wa/api/v1/template/msg', [
                    'channel'     => 'whatsapp',
                    'source'      => $source,
                    'destination' => $phone,
                    'src.name'    => $appName,
                    'template'    => json_encode(
                        $template,
                        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    ),
                ]);
        }, 3, 2000, 'gupshup.template', 20);

        $response = $attempt['response'];

        if ($response === null) {
            Log::error('Gupshup unreachable', [
                'destination' => $phone,
                'attempts'    => $attempt['attempts'],
                'error'       => optional($attempt['exception'])->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gupshup server se connection nahi ho paya. Thodi der baad dobara try karein.',
            ], 502);
        }

        /*
        |--------------------------------------------------------------------------
        | Read Gupshup Response
        |--------------------------------------------------------------------------
        */

        $responseData = $response->json();

        if (!is_array($responseData)) {
            $responseData = [];
        }

        Log::info('Gupshup WhatsApp template response', [
            'destination'   => $phone,
            'status_code'   => $response->status(),
            'template_id'   => $templateId,
            'listing_name'  => $listingName,
            'button_suffix' => $buttonSuffix,
            'template'      => $template,
            'response'      => !empty($responseData)
                ? $responseData
                : $response->body(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | HTTP Request Failed
        |--------------------------------------------------------------------------
        */

        if (!$response->successful()) {
            $transient = HttpRetry::retryable($response);

            return response()->json([
                'success' => false,
                'message' => $transient
                    // Gupshup's own wording for an overload is unhelpful here,
                    // and its status code must not become ours.
                    ? 'Gupshup abhi busy hai. Thodi der baad dobara try karein.'
                    : ($responseData['message']
                        ?? $responseData['reason']
                        ?? $responseData['error']
                        ?? 'Gupshup API ne message reject kar diya.'),
                'data' => !empty($responseData)
                    ? $responseData
                    : $response->body(),
            ], HttpRetry::clientStatus($response->status()));
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Gupshup Status
        |--------------------------------------------------------------------------
        */

        $status = strtolower(
            trim((string) ($responseData['status'] ?? ''))
        );

        $successfulStatuses = [
            'success',
            'submitted',
            'accepted',
            'queued',
        ];

        if (
            $status !== '' &&
            !in_array($status, $successfulStatuses, true)
        ) {
            return response()->json([
                'success' => false,
                'message' => $responseData['message']
                    ?? $responseData['reason']
                    ?? $responseData['error']
                    ?? 'WhatsApp template message submit nahi hua.',
                'status' => $status,
                'data'   => $responseData,
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Successful Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp template message successfully submit ho gaya.',
            'status'  => $status ?: 'submitted',

            'message_id' => $responseData['messageId']
                ?? $responseData['message_id']
                ?? null,

            'data' => $responseData,
        ], 202);

    } catch (ConnectionException $exception) {
        Log::error('Gupshup connection error', [
            'destination' => $phone,
            'error'       => $exception->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gupshup server se connection nahi ho paya. Dobara try karein.',
        ], 502);

    } catch (\Throwable $exception) {
        Log::error('WhatsApp template exception', [
            'destination' => $phone,
            'error'       => $exception->getMessage(),
            'file'        => $exception->getFile(),
            'line'        => $exception->getLine(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Message bhejte samay server error aa gaya.',
        ], 500);
    }
}

    /**
     * Sync GMB photos for a listing
     * Fetches top 5-10 photos from Google My Business
     */
    public function syncGmbPhotos(Request $request, $id)
    {
        try {
            $listing = Listing::findOrFail($id);

            // Initialize Google Places API
            $googlePlaces = new \App\Support\GooglePlaces();

            // Search for the business on Google Places
            $placeId = $googlePlaces->searchPlace($listing->name, $listing->location_id ? $listing->location->name : null);

            if (!$placeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'GMB profile nahi mila. Business name check karein.',
                ], 404);
            }

            // Get photos
            $photoUrls = $googlePlaces->getPhotos($placeId, 10);

            if (empty($photoUrls)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Koi photos nahi mile GMB profile mein.',
                ], 404);
            }

            // Download and save images
            $savedPhotos = [];
            foreach (array_slice($photoUrls, 0, 10) as $photoUrl) {
                try {
                    $imageContent = Http::get($photoUrl)->body();

                    if (!empty($imageContent)) {
                        // Save using UploadedFile
                        $filename = 'gmb_' . $listing->id . '_' . time() . '_' . rand(1000, 9999) . '.jpg';
                        $filepath = public_path('assets/images/listing/photos/' . $filename);

                        // Ensure directory exists
                        if (!is_dir(dirname($filepath))) {
                            mkdir(dirname($filepath), 0755, true);
                        }

                        file_put_contents($filepath, $imageContent);

                        // Convert to WebP
                        $webpFilename = \App\Support\Images\WebpImage::store(
                            new \Illuminate\Http\UploadedFile($filepath, $filename, 'image/jpeg', null, true),
                            'assets/images/listing/photos',
                            'gmb_' . $listing->id . '_' . time() . '_' . rand(1000, 9999)
                        );

                        $savedPhotos[] = 'assets/images/listing/photos/' . $webpFilename;
                    }
                } catch (\Exception $e) {
                    Log::error('GMB Photo Download Error: ' . $e->getMessage());
                    continue;
                }
            }

            // Update listing with GMB data
            $listing->update([
                'gmb_place_id' => $placeId,
                'gmb_photo_urls' => json_encode($savedPhotos),
                'gmb_last_sync' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => count($savedPhotos) . ' photos successfully synced from GMB!',
                'count' => count($savedPhotos),
            ]);

        } catch (\Throwable $e) {
            Log::error('GMB Sync Error: ' . $e->getMessage(), [
                'listing_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'GMB sync mein error aa gayi. Log check karein.',
            ], 500);
        }
    }

    /**
     * Get listings by category and location for bulk assignment
     * Excludes listings that already have the selected subcategory
     */
    public function bulkGetListings(Request $request)
    {
        $categoryId = $request->category_id;
        $locationId = $request->location_id;
        $subcategoryId = $request->subcategory_id;

        $query = Listing::where('status', 1);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $listings = $query->select('id', 'name', 'category_id', 'location_id', 'user_id', 'subcategory_ids')
            ->with(['category:id,name', 'location:id,name', 'user:id,username'])
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->map(function($listing) {
                return [
                    'id' => $listing->id,
                    'name' => $listing->name,
                    'user_name' => optional($listing->user)->username,
                    'category_name' => optional($listing->category)->name,
                    'location_name' => optional($listing->location)->name,
                ];
            });

        return response()->json(['listings' => $listings]);
    }

    /**
     * Bulk assign subcategories to listings (supports single or multiple)
     */
    public function bulkAssign(Request $request)
    {
        // Accept either subcategory_id (single) or subcategory_ids (array)
        $request->validate([
            'listing_ids' => 'required|array|min:1',
            'listing_ids.*' => 'required|integer|exists:listings,id',
        ]);

        // Support both single and multiple subcategory IDs
        $subcategoryIds = [];
        if ($request->has('subcategory_ids') && is_array($request->subcategory_ids)) {
            // Multiple subcategories (array)
            $subcategoryIds = array_map('intval', array_filter($request->subcategory_ids));
        } elseif ($request->has('subcategory_id')) {
            // Single subcategory
            $subcategoryIds = [(int)$request->subcategory_id];
        }

        if (empty($subcategoryIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one subcategory'
            ], 422);
        }

        // Verify all subcategories exist
        $existingCount = Subcategory::whereIn('id', $subcategoryIds)->count();
        if ($existingCount !== count($subcategoryIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected subcategories are invalid'
            ], 422);
        }

        $listingIds = array_map('intval', $request->listing_ids);

        try {
            $assignedCount = 0;

            foreach ($listingIds as $listingId) {
                $listing = Listing::findOrFail($listingId);

                // Get existing subcategory IDs
                $existingIds = $listing->subcategory_ids ?? [];
                if (is_string($existingIds)) {
                    $existingIds = json_decode($existingIds, true) ?? [];
                }

                // Add new subcategories if not already present (ADD mode only)
                $updated = false;
                foreach ($subcategoryIds as $subId) {
                    if (!in_array($subId, $existingIds)) {
                        $existingIds[] = $subId;
                        $updated = true;
                    }
                }

                if ($updated) {
                    $listing->subcategory_ids = $existingIds;
                    $listing->save();
                    $assignedCount++;
                }
            }

            $subCount = count($subcategoryIds);
            $message = "$assignedCount listing(s) updated with " . $subCount . " subcategory(ies)";

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function countListings(Request $request)
    {
        $categoryId = $request->input('category_id');
        $locationId = $request->input('location_id');

        $query = Listing::where('status', 1); // Only approved listings

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $count = $query->count();

        return response()->json(['count' => $count]);
    }
}
