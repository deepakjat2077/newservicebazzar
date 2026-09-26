<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileHandler;
use App\Models\SchemaSetting;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
	use FileHandler;

    public function index(){
    	return view('admin.setting.index');
    }

    public function update(Request $request){
    	$request->validate([
    		'site_name'=>'required',
    		'site_currency'=>'required',
    		'currency_symbol'=>'required',
    	]);
    	$setting = Setting::first();
    	$setting->update([
    		'site_name'=>$request->site_name,
    		'site_currency'=>$request->site_currency,
    		'currency_symbol'=>$request->currency_symbol,
    		'first_color'=>$request->first_color,
    		'second_color'=>$request->second_color,
            'tawk_chat'=>$request->tawk_chat,
            're_captcha'=>$request->re_captcha,
    		'registration'=>$request->registration ? 1 : 0,
    		'email_verification'=>$request->email_verification ? 1 : 0,
            'email_queue'=>$request->email_queue ? 1 : 0,
    		'multi_lang'=>$request->multi_lang ? 1 : 0,
    	]);
    	$alert = ['success','Setting updated successfully'];
    	return back()->withAlert($alert);
    }

    public function logoFav(){
    	return view('admin.setting.logofav');
    }

    public function logoFavUpdate(Request $request){
    	$request->validate([
    		'logo'=>'nullable|image',
            'logo_dark'=>'nullable|image',
    		'favicon'=>'nullable|image',
    	]);
    	if ($request->hasFile('logo')) {
    		try {
    			$data = [
    				'file'=>$request->logo,
    				'path'=>'assets/images/logo',
    			];
    			$filename = 'logo.png';
    			$this->uploadImage($data,$filename);
    		} catch (Exception $e) {
    			$alert = ['danger','Something wrong'];
    			return back()->withAlert($alert);
    		}
    	}
        if ($request->hasFile('logo_dark')) {
            try {
                $data = [
                    'file'=>$request->logo_dark,
                    'path'=>'assets/images/logo',
                ];
                $filename = 'logo_dark.png';
                $this->uploadImage($data,$filename);
            } catch (Exception $e) {
                $alert = ['danger','Something wrong'];
                return back()->withAlert($alert);
            }
        }
    	if ($request->hasFile('favicon')) {
    		try {
    			$data = [
    				'file'=>$request->favicon,
    				'path'=>'assets/images/logo',
    			];
    			$filename = 'favicon_new.png';
    			$this->uploadImage($data,$filename);
    		} catch (Exception $e) {
    			$alert = ['danger','Something wrong'];
    			return back()->withAlert($alert);
    		}
    	}

    	// Edit icon: used on listing details pages for owner-only edit button.
    	// Saved into public/assets/images (which is gitignored, same as the
    	// logo) so admin uploads survive `git pull` / `git clean` on the live
    	// server — the assets root is NOT ignored, so a file placed there was
    	// being wiped on every deploy.
    	// SVG files bypass image validation (getimagesize fails on SVG).
    	if ($request->hasFile('edit_icon')) {
    		try {
    			$file = $request->file('edit_icon');
    			$dir  = public_path('assets/images');
    			if (!is_dir($dir)) {
    				mkdir($dir, 0755, true);
    			}
    			// Remove any previously uploaded edit icon of a different
    			// extension so switching format (e.g. png -> svg) doesn't
    			// leave a stale file the view might pick up first.
    			foreach (['png','svg','jpg','jpeg','gif'] as $ext) {
    				$old = $dir . '/editicon.' . $ext;
    				if (file_exists($old)) {
    					@unlink($old);
    				}
    			}
    			$filename = 'editicon.' . $file->getClientOriginalExtension();
    			// Direct move without image processing for SVG/icon files
    			$file->move($dir, $filename);
    		} catch (Exception $e) {
    			$alert = ['danger','Something wrong'];
    			return back()->withAlert($alert);
    		}
    	}

    	$alert = ['success','Logo, Favicon & Edit Icon updated successfully'];
    	return back()->withAlert($alert);
    }

    public function seo(){
        return view('admin.setting.seo');
    }

    public function seoUpdate(Request $request){
        $request->validate([
            'keywords'=>'required|array',
            'meta_description'=>'required',
            'image'=>'nullable|image',
        ]);

        $setting = Setting::first();

        $image = @$setting->seo->image;
        if ($request->hasFile('image')) {
            try {
                $data = [
                    'file'=>$request->image,
                    'path'=>'assets/images/seo',
                   
                    'prevFile'=>$image,
                ];
                $image = $this->uploadFile('image',$data);
            } catch (Exception $e) {
                $alert = ['danger','Something wrong'];
                return back()->withAlert($alert);
            }
        }


        $setting->seo = [
            'keywords'=>$request->keywords,
            'meta_description'=>$request->meta_description,
            'image'=>$image,
        ];

        $setting->save();

        $alert = ['success','SEO Setting updated successfully'];
        return back()->withAlert($alert);
    }

    public function recaptcha(){
        return view('admin.setting.recaptcha');
    }

    public function recaptchaUpdate(Request $request)
    {
        $request->validate([
    		'site_key'=>'required',
    		'secret_key'=>'required',
    	]);
    	$setting = Setting::first();
    	$setting->update([
            'recaptcha'=>[
                'site_key'=>$request->site_key,
                'secret_key'=>$request->secret_key,
                'status'=>$request->status ? 1 : 0,

            ]
    	]);
    	$alert = ['success','Setting updated successfully'];
    	return back()->withAlert($alert);
    }

    /*
    |--------------------------------------------------------------------------
    | Gupshup (WhatsApp) API credentials
    |--------------------------------------------------------------------------
    |
    | Stored on the settings row and pushed into services.gupshup.* by
    | AppServiceProvider, so the whole WhatsApp flow (OTP + templates) can be
    | reconfigured from here without touching .env on the server.
    |
    */

    public function gupshup(){
        return view('admin.setting.gupshup');
    }

    public function gupshupUpdate(Request $request){
        $request->validate([
            // The API key is never re-displayed, so a blank submission means
            // "keep the stored value".
            'api_key'         => 'nullable|string|max:191',
            'app_name'        => 'required|string|max:191',
            'source'          => 'required|string|max:20',
            'template_id'     => 'nullable|string|max:191',
            'otp_template_id' => 'nullable|string|max:191',
            'enquiry_template_id' => 'nullable|string|max:191',

            // Pasting the callback URL in here is an easy mistake to make,
            // and it silently 401s every incoming webhook — so reject it.
            'webhook_secret'  => [
                'nullable', 'string', 'max:191',
                function ($attribute, $value, $fail) {
                    if (preg_match('#^\s*https?://#i', (string) $value)) {
                        $fail('The webhook secret must be a password you invent, not a URL. Paste the URL into Gupshup instead.');
                    }
                },
            ],
        ]);

        $setting = Setting::first();
        $current = (array) $setting->gupshup;

        $keep = function ($field) use ($request, $current) {
            return $request->filled($field)
                ? trim($request->input($field))
                : ($current[$field] ?? null);
        };

        $setting->gupshup = [
            'api_key'         => $keep('api_key'),
            'app_name'        => trim($request->app_name),
            // Country code + number, digits only (e.g. 919251067682).
            // Strip anything a "+91 92510 67682" style paste would add.
            'source'          => preg_replace('/\D/', '', $request->source),
            'template_id'     => trim((string) $request->template_id),
            'otp_template_id' => trim((string) $request->otp_template_id),
            'enquiry_template_id' => trim((string) $request->enquiry_template_id),
            // Editable and clearable: the form shows the current value, so an
            // empty box genuinely means "no secret" rather than "unchanged".
            'webhook_secret'  => trim((string) $request->webhook_secret) ?: null,
        ];

        $setting->save();

        $alert = ['success','Gupshup WhatsApp setting updated successfully'];
        return back()->withAlert($alert);
    }

    /**
     * The company details printed on every tax invoice — legal name, address,
     * GSTIN, CIN, PAN and the contact line. Kept here rather than in
     * config/invoice.php so they can be corrected without a deploy;
     * AppServiceProvider pushes whatever is saved into config('invoice.*').
     */
    public function invoice()
    {
        return view('admin.setting.invoice');
    }

    public function invoiceUpdate(Request $request)
    {
        $request->validate([
            'legal_name' => 'required|string|max:191',
            'trade_name' => 'nullable|string|max:191',
            'address'    => 'nullable|string|max:400',
            'city'       => 'nullable|string|max:100',
            'state'      => 'nullable|string|max:100',
            'state_code' => 'nullable|string|max:4',
            'pincode'    => 'nullable|string|max:10',
            // 22AAAAA0000A1Z5 — 15 characters, and a wrong one makes every
            // invoice issued against it invalid.
            'gstin'      => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'],
            'cin'        => ['nullable', 'string', 'max:25'],
            'pan'        => ['nullable', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i'],
            'phone'      => 'nullable|string|max:60',
            'email'      => 'nullable|email|max:191',
            'website'    => 'nullable|string|max:191',
            'sac'        => 'nullable|string|max:12',
            'gst_rate'   => 'nullable|numeric|min:0|max:100',
            'bank_name'  => 'nullable|string|max:120',
            'bank_account' => 'nullable|string|max:40',
            'bank_ifsc'  => 'nullable|string|max:20',
            'bank_branch' => 'nullable|string|max:120',
            'terms'      => 'nullable|string|max:2000',
        ], [
            'gstin.regex' => 'That does not look like a valid GSTIN (15 characters, e.g. 08AAAAA0000A1Z5).',
            'pan.regex'   => 'That does not look like a valid PAN (10 characters, e.g. AAAAA0000A).',
        ]);

        $setting = Setting::first();

        $text = fn ($field) => trim((string) $request->input($field)) ?: null;
        $upper = fn ($field) => ($v = trim((string) $request->input($field))) ? strtoupper($v) : null;

        $setting->invoice = [
            'legal_name' => trim($request->legal_name),
            'trade_name' => $text('trade_name'),
            'address'    => $text('address'),
            'city'       => $text('city'),
            'state'      => $text('state'),
            'state_code' => $text('state_code'),
            'pincode'    => $text('pincode'),
            'gstin'      => $upper('gstin'),
            'cin'        => $upper('cin'),
            'pan'        => $upper('pan'),
            'phone'      => $text('phone'),
            'email'      => $text('email'),
            'website'    => $text('website'),
            'sac'        => $text('sac'),
            'gst_rate'   => $request->filled('gst_rate') ? (float) $request->gst_rate : null,
            'bank_name'    => $text('bank_name'),
            'bank_account' => $text('bank_account'),
            'bank_ifsc'    => $upper('bank_ifsc'),
            'bank_branch'  => $text('bank_branch'),
            // One term per line.
            'terms'      => collect(preg_split('/\r\n|\r|\n/', (string) $request->terms))
                                ->map(fn ($l) => trim($l))->filter()->values()->all(),
        ];

        $setting->save();

        return back()->withAlert(['success', 'Invoice details updated successfully']);
    }

    /**
     * Firebase (FCM) push notification credentials for the mobile app —
     * managed here instead of .env, same pattern as gupshup above. The admin
     * pastes the Firebase service-account JSON (Firebase Console > Project
     * settings > Service accounts > Generate new private key); FcmService reads
     * it via services.fcm.* (pushed in by AppServiceProvider) to send push
     * through the FCM HTTP v1 API.
     */
    public function push(){
        return view('admin.setting.push');
    }

    public function pushUpdate(Request $request){
        $request->validate([
            'push_enabled'         => 'nullable|boolean',
            // Blank = keep the stored JSON (it is never re-displayed in full).
            'service_account_json' => 'nullable|string',
        ]);

        $setting = Setting::first();
        $current = (array) $setting->push_notification;

        $serviceAccount = $current['service_account'] ?? null;
        $projectId      = $current['project_id'] ?? null;

        // A freshly pasted JSON replaces the stored one; validate it is real
        // and carries the three fields FCM v1 needs.
        if ($request->filled('service_account_json')) {
            $decoded = json_decode(trim($request->service_account_json), true);

            if (!is_array($decoded)
                || empty($decoded['project_id'])
                || empty($decoded['client_email'])
                || empty($decoded['private_key'])) {
                return back()->withAlert(['danger', 'That is not a valid Firebase service-account JSON. Download it from Firebase Console > Project settings > Service accounts.']);
            }

            $serviceAccount = $decoded;
            $projectId      = $decoded['project_id'];
        }

        $setting->push_notification = [
            'enabled'         => $request->boolean('push_enabled'),
            'project_id'      => $projectId,
            'service_account' => $serviceAccount,
        ];
        $setting->save();

        return back()->withAlert(['success', 'Push notification setting updated successfully']);
    }

    public function SchemaSetting()
{
    $schemas = SchemaSetting::latest()->get();

    return view('admin.schema.index', compact('schemas'));
}

public function SchemaSettingstore(Request $request)
{
    $request->validate([
        'page' => 'required',
        'schema_json' => ['required', $this->validJsonRule()],
    ]);

    SchemaSetting::create([
        'page' => $request->page,
        'schema_json' => $this->sanitizeSchemaJson($request->schema_json),
        'status' => $request->status ? 1 : 0,
    ]);

    $notify[] = ['success', 'Schema Added Successfully'];

    return back()->withNotify($notify);
}


public function SchemaSettingupdate(Request $request, $id)
{
    $request->validate([
        'page' => 'required',
        'schema_json' => ['required', $this->validJsonRule()],
    ]);

    $schema = SchemaSetting::findOrFail($id);

    $schema->page = $request->page;
    $schema->schema_json = $this->sanitizeSchemaJson($request->schema_json);
    $schema->status = $request->status == 1 ? 1 : 0;

    $schema->save();

    $notify[] = ['success', 'Schema Updated Successfully'];

    return back()->withNotify($notify);
}

/**
 * Validation rule ensuring the submitted schema is syntactically valid
 * JSON before it's ever stored. schema_json is rendered raw (via `{!! !!}`)
 * inside a <script type="application/ld+json"> block on every matching
 * public page, so malformed/malicious input here has site-wide impact.
 */
protected function validJsonRule()
{
    return function ($attribute, $value, $fail) {
        json_decode((string) $value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $fail('The schema JSON must be valid JSON.');
        }
    };
}

/**
 * Defense-in-depth: even though the value is validated as JSON above,
 * neutralize any literal "</script" sequence so it can never break out of
 * the <script type="application/ld+json"> block it's rendered inside.
 */
protected function sanitizeSchemaJson($value)
{
    return str_ireplace('</script', '<\/script', (string) $value);
}
public function SchemaSettingdelete($id)
{
    $schema = SchemaSetting::findOrFail($id);

    $schema->delete();

    $notify[] = ['success', 'Schema Deleted Successfully'];

    return back()->withNotify($notify);
}

}
