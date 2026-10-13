<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response('ok', 200))->name('health');

/*
 * Retired duplicate city slugs -> the surviving one, 301.
 *
 * "surat-1" was a second Surat row in `locations` (id 61, created three
 * months after the real id 9). Both slugs served the same city with the
 * same titles, H1s and self-referencing canonicals, so Google saw ~800
 * duplicate URLs competing with the originals. The listings have been
 * merged onto id 9 and id 61 is status=0, which alone would turn every
 * /surat-1/... URL into a 404 — these redirects hand the accumulated link
 * equity to the surviving URL instead of dropping it.
 *
 * Registered above every other route so they win over the /{city}/{slug}
 * and /{slug} catch-alls at the bottom of this file. The {path} form keeps
 * the rest of the URL intact (/surat-1/physiotherapists ->
 * /surat/physiotherapists); query strings ride along automatically.
 */
$retiredCitySlugs = [
    'surat-1' => 'surat',
];

foreach ($retiredCitySlugs as $oldSlug => $newSlug) {
    Route::redirect($oldSlug, '/'.$newSlug, 301);
    Route::get($oldSlug.'/{path}', fn (string $path) => redirect('/'.$newSlug.'/'.$path, 301))
        ->where('path', '.*');
}

/*
 * Short aliases people and old backlinks type for pages that live under a
 * longer slug. Each target is a real 200 page; the short form used to fall
 * through to the /{slug} catch-all and 404. Exact-path 301s only — "/blog"
 * redirects but "/blog/{post}" (a real 200 blog post) is untouched, since
 * Route::redirect matches the exact path, not its children.
 *
 * Only slugs with a genuine destination are listed. "/how-it-works" and
 * "/advertise" have no equivalent page, so they stay 404 rather than
 * redirect to something unrelated.
 */
$aliasRedirects = [
    'about' => '/about-us',
    'terms' => '/terms-and-conditions',
    'blog'  => '/blogs',
];

foreach ($aliasRedirects as $from => $to) {
    Route::redirect($from, $to, 301);
}

// Sitemap page - directory of all cities, services, and links (HTML directory)
Route::get('/sitemap-page', [\App\Http\Controllers\SiteController::class, 'sitemap'])->name('sitemap.page');

// Sitemap API endpoints for interactive drill-down navigation
Route::get('/api/sitemap/popular-combinations', [\App\Http\Controllers\SitemapApiController::class, 'popularCombinations']);
Route::get('/api/sitemap/subcategory-combinations', [\App\Http\Controllers\SitemapApiController::class, 'subcategoryCombinations']);
Route::get('/api/city/{slug}/categories', [\App\Http\Controllers\SitemapApiController::class, 'cityCategories']);
Route::get('/api/city/{citySlug}/category/{categorySlug}/listings', [\App\Http\Controllers\SitemapApiController::class, 'cityListings']);

/*
 * AI-engine-readable site map (llmstxt.org). Registered up here, ahead of the
 * catch-all /{slug} route at the bottom of this file, so "llms.txt" is served
 * as this plain-text map instead of being treated as a page slug.
 */
Route::get('/llms.txt', [\App\Http\Controllers\LlmsController::class, 'index'])->name('llms.txt');
Route::get('/llms-full.txt', [\App\Http\Controllers\LlmsController::class, 'full'])->name('llms.full.txt');

/*
 * One URL to hit after every deploy on hosting where PHP-FPM can't be
 * restarted by hand: `php artisan view:clear` only clears Laravel's own
 * file-based caches, but with opcache.validate_timestamps off (common on
 * shared hosting for performance) the running PHP-FPM workers keep serving
 * the *compiled bytecode* of the old files from memory regardless — a
 * deployed change quietly does nothing until the process restarts. This
 * calls opcache_reset() (the in-process fix that doesn't need a restart)
 * alongside the usual artisan cache clears, all in one request.
 *
 * Gated by a token in the query string, not a route parameter, so it never
 * shows up as a path segment in any log or browser history entry by
 * accident. Set DEPLOY_REFRESH_TOKEN in .env to something long and random;
 * the route 404s (not just denies) when it's unset, so it can't be hit at
 * all on an environment that never opted in.
 */
Route::get('/deploy/refresh-cache', function (\Illuminate\Http\Request $request) {
    $expected = config('app.deploy_refresh_token');

    if (!$expected || !hash_equals($expected, (string) $request->query('token'))) {
        abort(404);
    }

    $opcacheReset = function_exists('opcache_reset') ? opcache_reset() : null;

    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');

    return response()->json([
        'status' => 'ok',
        'opcache_reset' => $opcacheReset === null ? 'not available (opcache extension off)' : ($opcacheReset ? 'done' : 'failed'),
        'artisan_caches_cleared' => true,
    ]);
})->name('deploy.refresh-cache');

/*
 * Old requests/links with a literal "/public" segment (from before
 * AppServiceProvider::stripPublicPrefixFromUrls() stopped the site from
 * generating them itself — this document root points straight at public/,
 * so a request like /public/delhi/hotel never got the .htaccess 301 that
 * would normally strip it, and Laravel's own router just 404s it since
 * "public" doesn't match any real {location}).
 *
 * A permanent redirect here fixes both sides: a visitor following an old
 * bookmark/backlink lands on the real page instead of a 404, and Google
 * gets a proper 301 -> canonical chain instead of a dead link still sitting
 * in its index.
 *
 * Only matters for requests that fall through to Laravel at all — any file
 * that genuinely exists under public/ (css, js, images, the storage
 * symlink) is served by nginx directly and never reaches this route.
 */
Route::get('/public/{path?}', function (\Illuminate\Http\Request $request, string $path = '') {
    $target = '/' . ltrim($path, '/');
    $query = $request->getQueryString();

    return redirect($query ? $target . '?' . $query : $target, 301);
})->where('path', '.*');

Route::get('/gupshup-test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Live route working',
    ]);
});
use App\Http\Controllers\GupshupWebhookController;
use App\Http\Middleware\VerifyCsrfToken;

Route::match(['GET', 'POST'], '/gupshup/webhook', [
    GupshupWebhookController::class,
    'handle',
])
->withoutMiddleware([VerifyCsrfToken::class])
->name('gupshup.webhook');

// Pincode lookup route - MUST be before the catch-all /{slug} route
// Registered here outside the main group so it doesn't get caught by wildcard routes
Route::middleware('web')->group(function () {
    Route::get('/pincode/{pin}', [\App\Http\Controllers\Admin\PincodeController::class, 'show'])
        ->where('pin', '[0-9]{4,10}')
        ->name('pincode.fetch');
});

// Badge guide (Verified/Trusted explanation) — registered before the
// {location}/{slug} & {slug} catch-alls so /about/badges isn't swallowed.
Route::middleware('web')->group(function () {
    Route::get('/about/badges', function () {
        $setting = \App\Models\Setting::first();
        return view('themes.servicebazzar.badge-guide', ['setting' => $setting]);
    })->name('badges.guide');
});

Route::namespace('App\Http\Controllers')->middleware(['web'])->group(function () {
Route::get('/jaipur/maintenance', function () {
    return redirect('/', 301);
});
    Route::get('/nearby-location-suggestions', 'ListingController@nearbyLocationSuggestions')
        ->name('nearby.location.suggestions');

    Route::get('services', 'Admin\ServiceController@index')->name('home.services.index');

    Route::get('/category-suggestions', 'ListingController@categorySuggestions')
        ->name('category.suggestions');



    
    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::namespace('Admin')->name('admin.')->prefix('admin')->group(function () {

        Route::namespace('Auth')->group(function () {
            Route::get('/', 'LoginController@showLoginForm')->name('login');
            Route::post('/', 'LoginController@login')->name('login.submit');
            Route::get('/logout', 'LoginController@logout')->name('logout');

            Route::name('password.')->prefix('password')->group(function () {
                Route::get('reset', 'ForgotPasswordController@showCodeRequestForm')->name('reset');
                Route::post('reset', 'ForgotPasswordController@sendResetCodeEmail')->middleware('throttle:5,1');
                Route::get('verify', 'ForgotPasswordController@verify')->name('verify');
                Route::post('verify-code', 'ForgotPasswordController@verifyCode')->middleware('throttle:5,1')->name('verify.code');
                Route::get('reset/{token}', 'ResetPasswordController@showResetForm')->name('reset.change');
                Route::post('reset/change', 'ResetPasswordController@reset')->middleware('throttle:5,1')->name('reset.submit');
            });
        });

        Route::middleware('admin')->group(function () {

            // Admin browser push subscriptions (Web Push / VAPID)
            Route::post('push/subscribe',    'AdminPushSubscriptionController@subscribe')->name('push.subscribe');
            Route::delete('push/unsubscribe', 'AdminPushSubscriptionController@unsubscribe')->name('push.unsubscribe');

            Route::get('dashboard', 'AdminController@dashboard')->name('dashboard');
            Route::get('dashboard/listing-export', 'AdminController@listingExport')->middleware('permission:listing.export')->name('dashboard.listing.export');

            // Admin Listing Details & GMB Photo Sync
            Route::get('listing/details/{id}', 'ListingController@details')->name('listing.details');
            Route::post('listing/details/{id}/sync-gmb-photos', 'ListingController@syncGmbPhotos')->name('listing.sync-gmb-photos');

            Route::prefix('analytics')->name('analytics.')->middleware('permission:analytics.view')->group(function () {
                Route::get('/', 'AnalyticsController@index')->name('index');
                Route::get('sources', 'AnalyticsController@sources')->name('sources');
                Route::get('campaigns', 'CampaignLinkController@index')->name('campaigns');
                Route::post('campaigns', 'CampaignLinkController@store')->name('campaigns.store');
                Route::delete('campaigns/{id}', 'CampaignLinkController@destroy')->name('campaigns.destroy');
                Route::get('export/excel', 'AnalyticsController@exportExcel')->name('export.excel');
                Route::get('export/csv', 'AnalyticsController@exportCsv')->name('export.csv');
                Route::get('export/pdf', 'AnalyticsController@exportPdf')->name('export.pdf');
            });

            Route::prefix('gsc')->name('gsc.')->group(function () {
                Route::get('/',          'GscController@index')->name('index');
                Route::get('/auth',      'GscController@auth')->name('auth');
                Route::get('/callback',  'GscController@callback')->name('callback');
                Route::delete('/disconnect', 'GscController@disconnect')->name('disconnect');
            });

            // Pinged by long admin forms so a slow one doesn't lose its
            // session — see AdminController@sessionPing.
            Route::get('session-ping', 'AdminController@sessionPing')->name('session.ping');

            Route::get('profile', 'AdminController@profile')->name('profile');
            Route::post('profile-update', 'AdminController@profileUpdate')->name('profile.update');
            Route::get('password-change', 'AdminController@profileChange')->name('password.change');
            Route::post('password-update', 'AdminController@passwordUpdate')->name('password.update');


            Route::name('agents.')->prefix('agents')->middleware('permission:agent.manage')->group(function () {
                Route::get('/', 'AdminController@agents')->name('index');
                Route::get('create', 'AdminController@agentCreate')->name('create');
                Route::post('store', 'AdminController@agentStore')->name('store');
                Route::get('edit/{id}', 'AdminController@agentEdit')->name('edit');
                Route::post('update/{id}', 'AdminController@agentUpdate')->name('update');
                Route::delete('delete/{id}', 'AdminController@delete')->name('delete');
            });


            // Plan subscriptions history
            Route::get('plans', 'PlanController@index')->name('plans.index');

            Route::name('categoryHeading.')->prefix('category-heading')->middleware('permission:category.manage')->group(function () {
                Route::get('/', 'AdminController@categoryHeadingIndex')->name('index');
                Route::get('/create', 'AdminController@categoryHeadingCreate')->name('create');
                Route::post('/store', 'AdminController@categoryHeadingStore')->name('store');
                Route::get('/edit/{id}', 'AdminController@categoryHeadingEdit')->name('edit');
                Route::post('/update/{id}', 'AdminController@categoryHeadingUpdate')->name('update');
                Route::get('/delete/{id}', 'AdminController@categoryHeadingDelete')->name('delete');
                Route::get('/status/{id}', 'AdminController@categoryHeadingStatus')->name('status');
            });

Route::name('guest.chats.')->prefix('guest-chats')->middleware('permission:guestchat.view')->group(function () {
    Route::get('/', 'GuestChatAdminController@index')->name('index');
    Route::get('/{conversation}', 'GuestChatAdminController@show')->name('show');
    Route::post('/{conversation}/send', 'GuestChatAdminController@send')->name('send');
    Route::get('/{conversation}/messages', 'GuestChatAdminController@messages')->name('messages');
});

Route::get('notification-counts', 'AdminController@notificationCounts')->name('notification.counts');

            Route::get('services', 'ServiceController@adminindex')->middleware('permission:service.manage')->name('services.index');
            Route::get('services/create', 'ServiceController@create')->middleware('permission:service.manage')->name('services.create');
            Route::post('services/store', 'ServiceController@store')->middleware('permission:service.manage')->name('services.store');
            Route::get('services/edit/{id}', 'ServiceController@edit')->middleware('permission:service.manage')->name('services.edit');
            Route::put('services/update/{id}', 'ServiceController@update')->middleware('permission:service.manage')->name('services.update');
            Route::delete('admin/services/delete/{id}', 'ServiceController@delete')->middleware('permission:service.manage')->name('admin.services.delete');


            Route::name('users.')->prefix('users')->middleware('permission:user.view')->group(function () {
                Route::get('register', 'UserController@showRegistrationForm')->name('register');
                Route::post('register', 'UserController@register');

                Route::get('verify-otp', 'UserController@showOtpForm')->name('otp.form');
                Route::post('verify-otp', 'UserController@verifyOtp')->middleware('throttle:5,1')->name('otp.verify');
                Route::post('resend-otp', 'UserController@resendOtp')->middleware('throttle:5,1')->name('otp.resend');

                Route::get('all', 'UserController@all')->name('all');
                Route::get('unverified', 'UserController@unverified')->name('unverified');
                Route::get('banned', 'UserController@banned')->name('banned');
                Route::get('details/{id}', 'UserController@details')->name('details');
                Route::post('update/{id}', 'UserController@update')->name('update');
                Route::post('balance-update/{id}', 'UserController@updateBalance')->name('update.balance');
                Route::get('send-email/{id}', 'UserController@sendMail')->name('sendMail');
                Route::post('send-email/{id}', 'UserController@submitMail');
                Route::delete('delete/{id}', 'UserController@delete')->name('delete');

                Route::get('deposit/{id}', 'UserController@deposit')->name('deposit');
                Route::get('balance-sheet/{id}', 'UserController@balanceSheet')->name('balanceSheet');
                Route::get('login/{id}', 'UserController@login')->name('login');
                // Called by the listing form's "Add New User" picker, so listing.create
                // is enough here even without full user.view access.
                Route::post('users/quick-store', 'UserController@quickStore')->withoutMiddleware('permission:user.view')->middleware('permission:user.manage,listing.create')->name('quick.store');
            });


            // KYC verifications from the mobile app — review & approve/reject.
            Route::name('kyc.')->prefix('kyc')->group(function () {
                Route::get('/', 'KycController@index')->middleware('permission:user.view')->name('index');
                Route::post('approve/{id}', 'KycController@approve')->middleware('permission:user.manage')->name('approve');
                Route::post('reject/{id}', 'KycController@reject')->middleware('permission:user.manage')->name('reject');
            });

            // "Get a Callback" requests from the mobile app's Help screen.
            Route::name('callback.')->prefix('callback')->group(function () {
                Route::get('/', 'CallbackController@index')->middleware('permission:user.view')->name('index');
                Route::post('handled/{id}', 'CallbackController@markHandled')->middleware('permission:user.manage')->name('handled');
            });


            Route::name('gateway.')->prefix('gateway')->middleware('permission:gateway.manage')->group(function () {
                Route::name('method.')->prefix('method')->group(function () {
                    Route::get('/', 'PaymentMethodController@index')->name('index');
                    Route::get('edit/{id}', 'PaymentMethodController@edit')->name('edit');
                    Route::post('update/{id}', 'PaymentMethodController@update')->name('update');
                    Route::get('offline/create', 'PaymentMethodController@offline')->name('offline');
                    Route::post('offline/store', 'PaymentMethodController@offlineStore')->name('offline.store');
                    Route::get('currencies', 'PaymentMethodController@currencies')->name('currencies');
                    Route::get('currency-update/{id}', 'PaymentMethodController@currencyEdit')->name('currencies.update');
                    Route::post('currency-update/{id}', 'PaymentMethodController@currencyUpdate');
                });
            });


            Route::name('deposit.')->prefix('deposit')->middleware('permission:deposit.view')->group(function () {
                Route::get('all', 'DepositController@all')->name('all');
                Route::get('pending', 'DepositController@pending')->name('pending');
                Route::get('approved', 'DepositController@approved')->name('approved');
                Route::get('rejected', 'DepositController@rejected')->name('rejected');
                Route::get('details/{id}', 'DepositController@details')->name('details');
                Route::post('action/{id}', 'DepositController@action')->name('action');
            });


            Route::name('category.')->prefix('category')->middleware('permission:category.view')->group(function () {
                Route::get('/', 'CategoryController@index')->name('index');
                Route::post('create', 'CategoryController@create')->name('create');
                Route::get('create', 'CategoryController@createPage')->name('create.page');
                Route::get('update/{id}', 'CategoryController@update')->name('update.page');
                Route::put('edit/{id}', 'CategoryController@edit')->name('edit');
                Route::delete('delete/{id}', 'CategoryController@delete')->name('delete');
                Route::get('icons/search', 'CategoryController@searchIcons')->name('icons.search');
                Route::post('icons/download', 'CategoryController@downloadIcon')->name('icons.download');
            });


            Route::name('subcategories.')->prefix('subcategories')->middleware('permission:subcategory.view')->group(function () {
                Route::get('/', 'SubcategoriesController@index')->name('index');
                Route::get('export', 'SubcategoriesController@export')->name('export');
                Route::post('create', 'SubcategoriesController@create')->name('create');
                Route::get('create', 'SubcategoriesController@createPage')->name('create.page');
                Route::get('bulk-add', 'SubcategoriesController@bulkAddPage')->name('bulk.page');
                Route::post('bulk-store', 'SubcategoriesController@bulkStore')->name('bulk.store');
                Route::get('update/{id}', 'SubcategoriesController@update')->name('update.page');
                Route::put('edit/{id}', 'SubcategoriesController@edit')->name('edit');
                Route::delete('delete/{id}', 'SubcategoriesController@delete')->name('delete');
                // Called by the listing form's subcategory picker.
                Route::post('quick-store', 'SubcategoriesController@quickStore')->withoutMiddleware('permission:subcategory.view')->middleware('permission:subcategory.manage,listing.create')->name('quick.store');
                Route::get('by-category/{id}', 'SubcategoriesController@byCategory')->name('by.category');
            });


            Route::name('location.')->prefix('location')->middleware('permission:location.view')->group(function () {
                Route::get('/', 'LocationController@index')->name('index');
                Route::post('create', 'LocationController@create')->name('create');
                Route::post('edit/{id}', 'LocationController@edit')->name('edit');
                Route::delete('delete/{id}', 'LocationController@delete')->name('delete');
            });


            Route::name('package.')->prefix('package')->middleware('permission:package.manage')->group(function () {
                Route::get('/', 'PackageController@index')->name('index');
                Route::post('store', 'PackageController@store')->name('store');
                Route::get('status/{id}', 'PackageController@status')->name('status');
                Route::post('update/{id}', 'PackageController@update')->name('update');
                Route::get('free/{id}', 'PackageController@activateFree')->name('free.activate');
                Route::delete('delete/{id}', 'PackageController@delete')->name('delete');

                Route::post('certificate/store', 'CertificatePackageController@store')->name('certificate.store');
                Route::post('certificate/update/{id}', 'CertificatePackageController@update')->name('certificate.update');
                Route::get('certificate/status/{id}', 'CertificatePackageController@status')->name('certificate.status');
                Route::delete('certificate/delete/{id}', 'CertificatePackageController@delete')->name('certificate.delete');
                Route::get('certificate/{id}/positions', 'CertificatePackageController@positions')->name('certificate.positions');
                Route::post('certificate/{id}/positions', 'CertificatePackageController@storePosition')->name('certificate.positions.store');
                Route::delete('certificate/{id}/positions', 'CertificatePackageController@deletePosition')->name('certificate.positions.delete');
                Route::get('certificate/area-suggest', 'CertificatePackageController@areaSuggest')->name('certificate.areas');
            });


            Route::name('listing.')->prefix('listing')->group(function () {

    Route::get('/', 'ListingController@index')
        ->name('index');

    Route::get('add', 'ListingController@add')
        ->name('add');

    // The photo goes up here in small pieces before the save is submitted, so
    // the save itself carries no image and no large body for the hosting
    // firewall to refuse. See ShieldedUploadController.
    Route::post('shielded-upload', '\App\Http\Controllers\Admin\ShieldedUploadController@store')
        ->name('shielded.upload');

    Route::post('listing-video-upload', 'ListingController@uploadVideo')
        ->name('listing.video.upload');

    // `shielded`: the listing forms post their fields base64-packed, and
    // reference the photo uploaded above by id, so the hosting firewall has
    // neither prose nor an upload to match against and stops 403-ing saves.
    // See DecodeShieldedPayload.
    Route::post('store', 'ListingController@store')
        ->middleware('shielded')
        ->name('store');

    Route::post('send-whatsapp', 'ListingController@sendWhatsApp')
        ->name('send.whatsapp');

    Route::get('used-subcategories', 'ListingController@usedSubcategories')
        ->name('used.subcategories');

    Route::get('count', 'ListingController@countListings')
        ->name('count');

    Route::post('ai-generate', 'ListingController@aiGenerate')
        ->name('ai.generate');

    Route::post('spell-fix', 'ListingController@spellFix')
        ->middleware('throttle:60,1')
        ->name('spell.fix');

    Route::get('edit/{id}', 'ListingController@edit')
        ->name('edit');

    Route::post('edit/{id}', 'ListingController@update')
        ->middleware('shielded')
        ->name('update');

    Route::get('pending', 'ListingController@pending')
        ->name('pending');

    Route::get('update-pending', 'ListingController@updatePending')
        ->name('update.pending');

    Route::get('approve-name-address/{id}', 'ListingController@approveNameAddress')
        ->name('approve.name.address');

    Route::get('reject-name-address/{id}', 'ListingController@rejectNameAddress')
        ->name('reject.name.address');

    Route::get('approved', 'ListingController@approved')
        ->name('approved');

    Route::get('rejected', 'ListingController@rejected')
        ->name('rejected');

    Route::get('details/{id}', 'ListingController@details')
        ->name('details');

    Route::post('action/{id}', 'ListingController@action')
        ->name('action');

    Route::get('image-remove/{id}', 'ListingController@imageRemove')
        ->name('imageRemove');

    Route::delete('delete/{id}', 'ListingController@delete')
        ->name('delete');

    Route::post('bulk/get-listings', 'ListingController@bulkGetListings')
        ->name('bulk.getListings');

    Route::post('bulk/assign', 'ListingController@bulkAssign')
        ->name('bulk.assign');
});


            Route::get('subscribers', 'SubscriberController@index')->middleware('permission:subscriber.view')->name('subscriber.index');
            Route::post('subscribers/remove/{id}', 'SubscriberController@remove')->middleware('permission:subscriber.view')->name('subscriber.remove');
            Route::get('subscribers/send-email', 'SubscriberController@sendMail')->middleware('permission:subscriber.mail')->name('subscriber.sendMail');
            Route::post('subscribers/send-email', 'SubscriberController@submitMail')->middleware('permission:subscriber.mail');


            Route::name('setting.')->prefix('setting')->middleware('permission:setting.manage')->group(function () {
                Route::get('/', 'SettingController@index')->name('index');
                Route::post('update', 'SettingController@update')->name('update');

                Route::get('recaptcha', 'SettingController@recaptcha')->name('recaptcha');
                Route::post('recaptcha/update', 'SettingController@recaptchaUpdate')->name('recaptcha.update');

                Route::get('logo-favicon', 'SettingController@logoFav')->name('logofav');
                Route::post('logo-favicon/update', 'SettingController@logoFavUpdate')->name('logofav.update');

                Route::get('gupshup', 'SettingController@gupshup')->name('gupshup');
                Route::post('gupshup/update', 'SettingController@gupshupUpdate')->name('gupshup.update');

                Route::get('invoice', 'SettingController@invoice')->name('invoice');
                Route::post('invoice/update', 'SettingController@invoiceUpdate')->name('invoice.update');

                Route::get('push', 'SettingController@push')->name('push');
                Route::post('push/update', 'SettingController@pushUpdate')->name('push.update');

                Route::get('social-login', 'SocialLoginSettingController@index')->name('social.login');
                Route::post('social-login/update', 'SocialLoginSettingController@update')->name('social.login.update');

                Route::get('seo-setting', 'SettingController@seo')->name('seo');
                Route::post('seo-setting', 'SettingController@seoUpdate');

                Route::get('email/setting', 'EmailController@index')->name('email.index');
                Route::post('email/setting/update', 'EmailController@emailUpdate')->name('email.update');
                Route::post('email/setting/update/template', 'EmailController@emailUpdateTemplate')->name('email.update.template');
                Route::post('email/test', 'EmailController@testMail')->name('email.test');
            });


            Route::get('login-history', 'HistoryController@loginHistory')->middleware('permission:history.view')->name('history.login');
            Route::get('admin-login-history', 'HistoryController@adminLoginHistory')->middleware('permission:history.view')->name('history.admin_login');


            Route::name('language.')->prefix('language')->middleware('permission:language.manage')->group(function () {
                Route::get('/', 'LanguageController@index')->name('index');
                Route::post('create', 'LanguageController@create')->name('create');
                Route::post('edit/{id}', 'LanguageController@edit')->name('edit');
                Route::post('remove/{id}', 'LanguageController@remove')->name('remove');
                Route::get('translate/{id}', 'LanguageController@translate')->name('translate');
                Route::post('translate/{id}', 'LanguageController@translateUpdate');
            });


            Route::name('theme.')->prefix('theme')->middleware('permission:theme.manage')->group(function () {
                Route::get('item/{key}', 'ThemeController@item')->name('item');
                Route::get('item/element/create/{key}', 'ThemeController@create')->name('element.create');
                Route::get('item/element/edit/{key}/{id}', 'ThemeController@edit')->name('element.edit');
                Route::post('item/element/{id?}', 'ThemeController@elementSave')->name('element');
                Route::post('item/element/remove/{id}', 'ThemeController@remove')->name('element.remove');
            });


            Route::get('social-sites', 'SocialSiteController@index')->middleware('permission:social.manage')->name('social.sites.index');
            Route::get('social-sites/create', 'SocialSiteController@create')->middleware('permission:social.manage')->name('social.sites.create');
            Route::post('social-sites/store', 'SocialSiteController@store')->middleware('permission:social.manage')->name('social.sites.store');
            Route::get('social-sites/edit/{id}', 'SocialSiteController@edit')->middleware('permission:social.manage')->name('social.sites.edit');
            Route::put('social-sites/update/{id}', 'SocialSiteController@update')->middleware('permission:social.manage')->name('social.sites.update');
            Route::delete('social-sites/delete/{id}', 'SocialSiteController@delete')->middleware('permission:social.manage')->name('social.sites.delete');


            Route::get('seasonal-sections', 'SeasonalSectionController@index')->middleware('permission:seasonal.manage')->name('seasonal.sections.index');
Route::get('seasonal-sections/create', 'SeasonalSectionController@create')->middleware('permission:seasonal.manage')->name('seasonal.sections.create');
Route::post('seasonal-sections/store', 'SeasonalSectionController@store')->middleware('permission:seasonal.manage')->name('seasonal.sections.store');
Route::get('seasonal-sections/edit/{id}', 'SeasonalSectionController@edit')->middleware('permission:seasonal.manage')->name('seasonal.sections.edit');
Route::put('seasonal-sections/update/{id}', 'SeasonalSectionController@update')->middleware('permission:seasonal.manage')->name('seasonal.sections.update');
Route::delete('seasonal-sections/delete/{id}', 'SeasonalSectionController@delete')->middleware('permission:seasonal.manage')->name('seasonal.sections.delete');

Route::name('seo.')->prefix('seo-audit')->middleware('permission:seo.audit')->group(function () {
    Route::get('/', 'SeoAuditController@index')->name('audit');
    Route::post('rescan', 'SeoAuditController@rescan')->name('audit.rescan');
});

Route::name('roles.')->prefix('roles')->middleware('permission:role.manage')->group(function () {
    Route::get('/', 'RoleController@index')->name('index');
    Route::get('create', 'RoleController@create')->name('create');
    Route::post('store', 'RoleController@store')->name('store');
    Route::get('edit/{id}', 'RoleController@edit')->name('edit');
    Route::post('update/{id}', 'RoleController@update')->name('update');
    Route::delete('delete/{id}', 'RoleController@delete')->name('delete');
    Route::get('members/{id}', 'RoleController@members')->name('members');
});

Route::get('schema-settings', 'SettingController@SchemaSetting')->middleware('permission:schema.manage')->name('schema.index');
Route::post('schema-settings/store', 'SettingController@SchemaSettingstore')->middleware('permission:schema.manage')->name('schema.store');
Route::put('schema-settings/update/{id}', 'SettingController@SchemaSettingupdate')->middleware('permission:schema.manage')->name('schema.update');
Route::delete('schema-settings/delete/{id}', 'SettingController@SchemaSettingdelete')->middleware('permission:schema.manage')->name('schema.delete');


Route::post('editor-image', 'EditorImageController@store')->name('editor.image')->middleware('throttle:30,1');
Route::get('blog-comments', 'BlogCommentController@index')->middleware('permission:theme.manage')->name('blog.comments.index');
Route::delete('blog-comments/{id}', 'BlogCommentController@destroy')->middleware('permission:theme.manage')->name('blog.comments.delete');

Route::get('home-content-sections', 'HomeContentSectionController@index')->middleware('permission:home.manage')->name('home.content.sections.index');
Route::post('home-content-sections/store', 'HomeContentSectionController@store')->middleware('permission:home.manage')->name('home.content.sections.store');
Route::put('home-content-sections/update/{id}', 'HomeContentSectionController@update')->middleware('permission:home.manage')->name('home.content.sections.update');
Route::delete('home-content-sections/delete/{id}', 'HomeContentSectionController@delete')->middleware('permission:home.manage')->name('home.content.sections.delete');



        // Sales Genio — user management (inside admin+namespace group)
        Route::name('sales_users.')->prefix('sales-users')->group(function () {
            Route::get('/',                   'SalesUsersController@index')->name('index');
            Route::get('/create',             'SalesUsersController@create')->name('create');
            Route::post('/',                  'SalesUsersController@store')->name('store');
            Route::get('/{salesUser}/edit',   'SalesUsersController@edit')->name('edit');
            Route::put('/{salesUser}',        'SalesUsersController@update')->name('update');
            Route::delete('/{salesUser}',     'SalesUsersController@destroy')->name('destroy');
            Route::post('/{salesUser}/toggle','SalesUsersController@toggleStatus')->name('toggle');
        });

        // Home page "Featured Listings", picked by hand — no payment involved
        Route::name('home_featured.')->prefix('featured-listings')->middleware('permission:listing.edit')->group(function () {
            Route::get('/',                 'HomeFeaturedController@index')->name('index');
            Route::get('/search',           'HomeFeaturedController@search')->name('search');
            Route::post('/',                'HomeFeaturedController@store')->name('store');
            Route::post('/{id}',            'HomeFeaturedController@update')->whereNumber('id')->name('update');
            Route::post('/{id}/toggle',     'HomeFeaturedController@toggle')->whereNumber('id')->name('toggle');
            Route::post('/{id}/move/{dir}', 'HomeFeaturedController@move')->whereNumber('id')->whereIn('dir', ['up', 'down'])->name('move');
            Route::delete('/{id}',          'HomeFeaturedController@destroy')->whereNumber('id')->name('destroy');
        });

        // Sales Genio — discounts the sales team can pick from in the cart
        Route::name('sales_discounts.')->prefix('sales-discounts')->middleware('permission:package.manage')->group(function () {
            Route::get('/',               'SalesDiscountController@index')->name('index');
            Route::get('/create',         'SalesDiscountController@create')->name('create');
            Route::post('/',              'SalesDiscountController@store')->name('store');
            Route::get('/{id}/edit',      'SalesDiscountController@edit')->name('edit');
            Route::post('/{id}',          'SalesDiscountController@update')->name('update');
            Route::post('/{id}/toggle',   'SalesDiscountController@toggle')->name('toggle');
            Route::delete('/{id}',        'SalesDiscountController@destroy')->name('destroy');
        });

        // Sales Genio — KYC document review
        Route::name('sales_kyc.')->prefix('sales-kyc')->group(function () {
            Route::get('/',                          'SalesKycController@index')->name('index');
            Route::get('/{id}',                      'SalesKycController@show')->name('show');
            Route::post('/{id}/doc/{docId}/status',  'SalesKycController@updateStatus')->name('status');
            Route::post('/{id}/doc/{docId}/ai-read', 'SalesKycController@aiRead')->name('ai_read');
            Route::post('/{id}/doc/{docId}/number',  'SalesKycController@saveNumber')->name('number');
        });

        // Sales Genio — disposition monitoring
        Route::get('sales-dispositions', 'SalesDispositionController@index')->name('sales_dispositions.index');
        Route::delete('sales-dispositions/{id}', 'SalesDispositionController@destroy')->name('sales_dispositions.destroy');
        Route::get('sales-team', 'SalesTeamController@index')->name('sales_team.index');
        Route::get('sales-team/{id}', 'SalesTeamController@show')->whereNumber('id')->name('sales_team.show');

        });

        Route::name('service-keywords.')->prefix('service-keywords')->middleware(['admin', 'permission:keyword.manage'])->group(function () {
    Route::get('/', 'ServiceKeywordController@index')->name('index');
    Route::get('create', 'ServiceKeywordController@create')->name('create');
    Route::post('store', 'ServiceKeywordController@store')->name('store');
    Route::get('edit/{id}', 'ServiceKeywordController@edit')->name('edit');
    Route::post('update/{id}', 'ServiceKeywordController@update')->name('update');
    Route::get('delete/{id}', 'ServiceKeywordController@delete')->name('delete');
    Route::get('status/{id}', 'ServiceKeywordController@status')->name('status');
});

    });


    /*
    |--------------------------------------------------------------------------
    | Extra Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {

        Route::name('banners.')->prefix('banners')->middleware('permission:banner.manage')->group(function () {
            Route::get('/', 'BannerController@index')->name('index');
            Route::post('store', 'BannerController@store')->name('store');
            Route::get('edit/{id}', 'BannerController@edit')->name('edit');
            Route::post('update/{id}', 'BannerController@update')->name('update');
            Route::delete('delete/{id}', 'BannerController@delete')->name('delete');
            Route::get('toggle/{id}', 'BannerController@toggle')->name('toggle');
        });


        // Get Listed screen — showcase listings the admin picks for the app.
        Route::name('showcase.')->prefix('showcase')->middleware('permission:banner.manage')->group(function () {
            Route::get('/', 'ShowcaseController@index')->name('index');
            Route::post('store', 'ShowcaseController@store')->name('store');
            Route::get('toggle/{id}', 'ShowcaseController@toggle')->name('toggle');
            Route::get('move/{id}/{dir}', 'ShowcaseController@move')->name('move')->where('dir', 'up|down');
            Route::delete('delete/{id}', 'ShowcaseController@delete')->name('delete');
        });

        // Client success stories (testimonials) for the Get Listed screen.
        Route::name('success-stories.')->prefix('success-stories')->middleware('permission:banner.manage')->group(function () {
            Route::get('/', 'SuccessStoryController@index')->name('index');
            Route::post('store', 'SuccessStoryController@store')->name('store');
            Route::get('edit/{id}', 'SuccessStoryController@edit')->name('edit');
            Route::post('update/{id}', 'SuccessStoryController@update')->name('update');
            Route::get('toggle/{id}', 'SuccessStoryController@toggle')->name('toggle');
            Route::delete('delete/{id}', 'SuccessStoryController@delete')->name('delete');
        });


        Route::name('hbb.')->prefix('hbb')->middleware('permission:hbb.manage')->group(function () {
            Route::get('/', 'hbbController@index')->name('index');
            Route::post('store', 'hbbController@store')->name('store');
            Route::get('edit/{id}', 'hbbController@edit')->name('edit');
            Route::post('update/{id}', 'hbbController@update')->name('update');
            Route::delete('delete/{id}', 'hbbController@delete')->name('delete');
            Route::get('toggle/{id}', 'hbbController@toggle')->name('toggle');
        });


       Route::get('/enquiries', 'EnquiryController@index')->middleware('permission:enquiry.view')
    ->name('enquiry.index');

Route::get('/enquiry/view/{id}', 'EnquiryController@view')->middleware('permission:enquiry.view')
    ->name('enquiry.view');

Route::get('/enquiry/read/{id}', 'EnquiryController@markAsRead')->middleware('permission:enquiry.manage')
    ->name('enquiry.read');

Route::delete('/enquiry/delete/{id}', 'EnquiryController@delete')->middleware('permission:enquiry.manage')
    ->name('enquiry.delete');

Route::get('/enquiries/export', 'EnquiryController@exportEnquiries')->middleware('permission:enquiry.view')
    ->name('enquiry.export');

Route::get('/enquiry-clicks', 'EnquiryController@index2')->middleware('permission:enquiry.view')
    ->name('enquiry.clicks');

Route::get('/enquiry-clicks/export', 'EnquiryController@exportClicks')->middleware('permission:enquiry.view')
    ->name('enquiry.clicks.export');

Route::get('/enquiry/generate', 'EnquiryController@generateForm')->middleware('permission:enquiry.manage')
    ->name('enquiry.generate');
Route::post('/enquiry/generate', 'EnquiryController@generateStore')->middleware('permission:enquiry.manage')
    ->name('enquiry.generate.store');
Route::get('/enquiry/listings-by-category', 'EnquiryController@generateListingsByCategory')->middleware('permission:enquiry.manage')
    ->name('enquiry.listings.by.category');

            /*
            |------------------------------------------------------------------
            | WhatsApp panel
            |------------------------------------------------------------------
            |
            | Reading the panel needs whatsapp.view; anything that actually
            | puts a message on the wire needs whatsapp.send on top.
            |
            */
            Route::name('whatsapp.')->prefix('whatsapp')->group(function () {
                Route::middleware('permission:whatsapp.view')->group(function () {
                    Route::get('/', 'Admin\WhatsappController@index')->name('index');
                    Route::get('listings', 'Admin\WhatsappController@listings')->name('listings');
                    Route::get('enquiries', 'Admin\WhatsappController@enquiries')->name('enquiries');
                    Route::get('enquiry/{id}', 'Admin\WhatsappController@enquiry')->name('enquiry');
                    Route::get('sent-report', 'Admin\WhatsappController@sentReport')->name('sent.report');
                    Route::get('chats', 'Admin\WhatsappController@chats')->name('chats');
                    Route::get('chat/{phone}', 'Admin\WhatsappController@chat')->name('chat');
                    Route::get('chat/{phone}/messages', 'Admin\WhatsappController@chatMessages')->name('chat.messages');

                    Route::get('replies', 'Admin\WhatsappController@replies')->name('replies');
                    Route::get('replies/unread-count', 'Admin\WhatsappController@unreadCount')->name('replies.count');
                    Route::post('replies/{id}/read', 'Admin\WhatsappController@markReplyRead')->name('replies.read');
                    Route::post('replies/read-all', 'Admin\WhatsappController@markAllRepliesRead')->name('replies.readall');
                });

                Route::post('send', 'Admin\WhatsappController@send')
                    ->middleware('permission:whatsapp.send')
                    ->name('send');

                Route::post('chat/{phone}/reply', 'Admin\WhatsappController@replyToChat')
                    ->middleware('permission:whatsapp.send')
                    ->name('chat.reply');

                Route::middleware('permission:whatsapp.delete')->group(function () {
                    Route::delete('message/{id}', 'Admin\WhatsappController@deleteMessage')
                        ->name('message.delete');

                    Route::delete('chat/{phone}', 'Admin\WhatsappController@deleteChat')
                        ->name('chat.delete');
                });
            });
    });


    Route::get('services/{slug}', 'Admin\ServiceController@show')->name('services.show');


    /*
    |--------------------------------------------------------------------------
    | User Routes
    |--------------------------------------------------------------------------
    */

    Route::name('user.')->prefix('user')->group(function () {

        // Redirect login page to home with popup trigger
        Route::get('login', function () {
            return redirect('/')->with('showAuthPopup', true);
        })->name('login');

        Route::post('login', 'Auth\LoginController@login')->middleware('throttle:5,1');

        Route::get('otp-login', 'Auth\OtpLoginController@showOtpLoginForm')->name('otp.login.form');
        Route::post('otp-login/send', 'Auth\OtpLoginController@sendOtp')->middleware('throttle:5,1')->name('otp.login.send');
        Route::get('otp-login/verify', 'Auth\OtpLoginController@showVerifyForm')->name('otp.login.verify.form');
        Route::post('otp-login/verify', 'Auth\OtpLoginController@verifyOtp')->middleware('throttle:5,1')->name('otp.login.verify');



        // Raised from 5/min — each send costs a real Gupshup WhatsApp
        // message, so this stays in place rather than being removed
        // outright, just loose enough that normal repeated testing/retries
        // don't trip it the way 5/min did.
        Route::post('quick-auth/send', 'Auth\RegisterController@quickAuthSend')->middleware('throttle:20,1')->name('quick.auth.send');
Route::post('quick-auth/verify', 'Auth\RegisterController@quickAuthVerify')->middleware('throttle:20,1')->name('quick.auth.verify');
Route::post('quick-auth/resend', 'Auth\RegisterController@quickAuthResend')->middleware('throttle:20,1')->name('quick.auth.resend');

        Route::get('social-login/{provider}/redirect', 'Auth\SocialLoginController@redirect')
            ->where('provider', 'google|facebook')
            ->name('social.login.redirect');
        Route::get('social-login/{provider}/callback', 'Auth\SocialLoginController@callback')
            ->where('provider', 'google|facebook')
            ->name('social.login.callback');

        Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
        Route::post('register', 'Auth\RegisterController@register');

        Route::get('verify-otp', 'Auth\RegisterController@showOtpForm')->name('otp.form');
        Route::post('verify-otp', 'Auth\RegisterController@verifyOtp')->middleware('throttle:5,1')->name('otp.verify');
        Route::post('resend-otp', 'Auth\RegisterController@resendOtp')->middleware('throttle:5,1')->name('otp.resend');
        Route::get('logout', 'Auth\LoginController@logout')->name('logout');

        Route::name('password.')->prefix('password')->group(function () {
            Route::get('reset', 'Auth\ForgotPasswordController@showCodeRequestForm')->name('reset');
            Route::post('reset', 'Auth\ForgotPasswordController@sendResetCodeEmail')->middleware('throttle:5,1');
            Route::get('verify', 'Auth\ForgotPasswordController@verify')->name('verify');
            Route::post('verify-code', 'Auth\ForgotPasswordController@verifyCode')->middleware('throttle:5,1')->name('verify.code');
            Route::get('reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('reset-code');
            Route::post('reset/change', 'Auth\ResetPasswordController@reset')->middleware('throttle:5,1')->name('change');
        });


        // Public user profile view: /user/profile/deepak-choudhary
        // Placed above the auth block so it stays publicly accessible. The
        // {username} segment can't collide with the auth-only `profile`
        // (GET /user/profile) route since that one has no third segment.
        Route::get('profile/{username}', 'SiteController@userProfile')
            ->name('profile.view')
            ->where('username', '[a-z0-9_-]+');

        Route::middleware('auth')->group(function () {

            Route::get('verification', 'Auth\VerificationController@index')->name('verification');
            Route::get('verify/resend', 'Auth\VerificationController@verifyResend')->name('verify.resend');
            Route::get('verify/{hash}', 'Auth\VerificationController@verify')->name('verify');
            Route::get('verification/banned', 'Auth\VerificationController@banned')->name('banned');

            Route::middleware('verified')->group(function () {

                Route::get('dashboard', 'UserController@dashboard')->name('dashboard');

                Route::get('profile', 'UserController@profile')->name('profile');
                Route::post('profile', 'UserController@profileUpdate');
                Route::post('profile/mobile-2/send-otp', 'UserController@sendMobile2Otp')->middleware('throttle:5,1')->name('profile.mobile2.send');
                Route::post('profile/mobile-2/verify-otp', 'UserController@verifyMobile2Otp')->middleware('throttle:10,1')->name('profile.mobile2.verify');

                Route::post('profile/address', 'UserController@addressStore')->name('profile.address.store');
                Route::delete('profile/address/{address}', 'UserController@addressDestroy')->name('profile.address.destroy');
                Route::post('profile/favorite-listings', 'UserController@favoriteListings')->name('profile.favorite.listings');
                Route::post('profile/favorite-listing-toggle', 'UserController@favoriteListingToggle')->name('profile.favorite.listing.toggle');

                Route::get('schedules', 'UserController@schedules')->name('schedules');
                Route::get('schedule-removed/{id}', 'UserController@scheduleRemoved')->name('schedule.removed');
                Route::post('schedules', 'UserController@schedulesAdd');

                Route::get('social', 'UserController@social')->name('social');
                Route::post('social', 'UserController@socialUpdate');

                Route::get('password-change', 'UserController@passwordChange')->name('password.edit');
                Route::post('password-update', 'UserController@passwordUpdate')->name('password.update');

                Route::get('deposit', 'DepositController@deposits')->name('deposit');
                Route::get('payment/done', 'DepositController@paymentDone')->name('payment.done');
                Route::post('deposit/submit', 'DepositController@depositSubmit')->name('deposit.submit');
                Route::get('deposit/confirm', 'DepositController@depositConfirm')->name('deposit.confirm');
                Route::get('deposit/details/{id}', 'DepositController@depositDetails')->name('deposit.details');
                Route::get('deposit/{id}/invoice', 'DepositController@invoice')->whereNumber('id')->name('deposit.invoice');


                Route::name('listing.')->prefix('listing')->group(function () {
                    Route::get('/', 'ListingController@index')->name('index');
                    Route::get('create', 'ListingController@create')->name('create');
                    Route::post('store', 'ListingController@store')->name('store');
                    Route::get('edit/{id}', 'ListingController@edit')->name('edit');
                    Route::post('update/{id}', 'ListingController@update')->name('update');
                    Route::get('image-remove/{id}', 'ListingController@imageRemove')->name('imageRemove');
                    Route::put('updatesection/{id}', 'ListingController@updatesection')->name('updatesection');

                    Route::get('import-leads/sample', 'ListingController@importLeadsSample')->name('import-leads.sample');
                    Route::get('{id}/dashboard', 'ListingController@businessDashboard')->name('dashboard');
                    Route::get('{id}/manage', 'ListingController@manage')->name('manage');
                    Route::get('{id}/profile', 'ListingController@ownerProfile')->name('profile');
                    // The SB Sales edit pages, used by the owner for their own listing (see OwnerEditController).
                    Route::get('{id}/edit/name', 'OwnerEditController@proxy')->defaults('sg', 'editName')->name('sg.name');
                    Route::post('{id}/edit/name', 'OwnerEditController@proxy')->defaults('sg', 'updateName')->name('sg.name.update');
                    Route::get('{id}/edit/address', 'OwnerEditController@proxy')->defaults('sg', 'editAddress')->name('sg.address');
                    Route::post('{id}/edit/address', 'OwnerEditController@proxy')->defaults('sg', 'updateAddress')->name('sg.address.update');
                    Route::get('{id}/edit/timings', 'OwnerEditController@proxy')->defaults('sg', 'editTimings')->name('sg.timings');
                    Route::post('{id}/edit/timings', 'OwnerEditController@proxy')->defaults('sg', 'updateTimings')->name('sg.timings.update');
                    Route::get('{id}/edit/categories', 'OwnerEditController@proxy')->defaults('sg', 'editCategories')->name('sg.categories');
                    Route::post('{id}/edit/categories', 'OwnerEditController@proxy')->defaults('sg', 'updateCategories')->name('sg.categories.update');
                    Route::get('{id}/edit/establishment', 'OwnerEditController@proxy')->defaults('sg', 'editEstablishment')->name('sg.establishment');
                    Route::post('{id}/edit/establishment', 'OwnerEditController@proxy')->defaults('sg', 'updateEstablishment')->name('sg.establishment.update');
                    Route::get('{id}/edit/website', 'OwnerEditController@proxy')->defaults('sg', 'editWebsite')->name('sg.website');
                    Route::post('{id}/edit/website', 'OwnerEditController@proxy')->defaults('sg', 'updateWebsite')->name('sg.website.update');
                    Route::get('{id}/edit/social', 'OwnerEditController@proxy')->defaults('sg', 'editSocial')->name('sg.social');
                    Route::post('{id}/edit/social', 'OwnerEditController@proxy')->defaults('sg', 'updateSocial')->name('sg.social.update');
                    Route::get('{id}/edit/map', 'OwnerEditController@proxy')->defaults('sg', 'editMap')->name('sg.map');
                    Route::post('{id}/edit/map', 'OwnerEditController@proxy')->defaults('sg', 'updateMap')->name('sg.map.update');
                    Route::get('{id}/edit/awards', 'OwnerEditController@proxy')->defaults('sg', 'editAwards')->name('sg.awards');
                    Route::post('{id}/edit/awards', 'OwnerEditController@proxy')->defaults('sg', 'updateAwards')->name('sg.awards.update');
                    Route::get('{id}/edit/certificates', 'OwnerEditController@proxy')->defaults('sg', 'editCertificates')->name('sg.certificates');
                    Route::post('{id}/edit/certificates', 'OwnerEditController@proxy')->defaults('sg', 'updateCertificates')->name('sg.certificates.update');
                    Route::get('{id}/edit/language', 'OwnerEditController@proxy')->defaults('sg', 'editLanguage')->name('sg.language');
                    Route::post('{id}/edit/language', 'OwnerEditController@proxy')->defaults('sg', 'updateLanguage')->name('sg.language.update');
                    Route::get('{id}/edit/photos', 'OwnerEditController@proxy')->defaults('sg', 'photoGallery')->name('sg.photos');
                    Route::post('{id}/edit/photos', 'OwnerEditController@proxy')->defaults('sg', 'uploadPhotos')->name('sg.photos.upload');
                    Route::delete('{id}/edit/photos', 'OwnerEditController@proxy')->defaults('sg', 'deletePhoto')->name('sg.photos.delete');
                    Route::post('{id}/edit/video-poster', 'OwnerEditController@proxy')->defaults('sg', 'videoPoster')->name('sg.video.poster');
                    Route::post('{id}/edit/photos/cover', 'OwnerEditController@proxy')->defaults('sg', 'setCoverPhoto')->name('sg.photos.cover');
                    Route::get('{id}/edit/catalogue', 'OwnerEditController@proxy')->defaults('sg', 'catalogue')->name('sg.catalogue');
                    Route::post('{id}/edit/catalogue', 'OwnerEditController@proxy')->defaults('sg', 'uploadCatalogue')->name('sg.catalogue.upload');
                    Route::post('{id}/edit/catalogue/{image}/delete', 'OwnerEditController@proxy')->defaults('sg', 'deleteCatalogue')->name('sg.catalogue.delete');
                    Route::get('{id}/edit/kyc', 'OwnerEditController@proxy')->defaults('sg', 'editKyc')->name('sg.kyc');
                    Route::post('{id}/edit/kyc', 'OwnerEditController@proxy')->defaults('sg', 'uploadKyc')->name('sg.kyc.upload');
                    Route::post('{id}/edit/kyc/number', 'OwnerEditController@proxy')->defaults('sg', 'saveKycNumber')->name('sg.kyc.number');
                    Route::delete('{id}/edit/kyc/{doc}', 'OwnerEditController@proxy')->defaults('sg', 'deleteKyc')->name('sg.kyc.delete');
                    Route::get('edit/subcategories-by-category', [\App\Http\Controllers\SalesGenioController::class, 'subcategoriesByCategory'])->name('sg.subcategories');
                    Route::get('{id}/advertise', 'ListingController@advertise')->name('advertise');
                    Route::get('{id}/kyc', 'ListingController@businessKyc')->name('kyc');
                    Route::post('{id}/kyc', 'ListingController@businessKycUpload')->name('kyc.upload');
                    Route::post('{id}/kyc/number', 'ListingController@businessKycNumber')->name('kyc.number');
                    Route::delete('{id}/kyc/{doc}', 'ListingController@businessKycDelete')->name('kyc.delete');
                    Route::get('{id}/advertise/checkout/{package}', 'ListingController@advertiseCheckout')->name('advertise.checkout');
                    Route::get('advertise-areas', 'ListingController@advertiseAreas')->name('advertise.areas');
                    Route::get('{id}/advertise/package/{pkg}', 'ListingController@advertiseSalesSelect')->name('advertise.sales.select');
                    Route::get('{id}/advertise/package/{pkg}/design', 'ListingController@advertiseDesign')->name('advertise.design');
                    Route::get('{id}/advertise/design-preview/{template}', 'OwnerEditController@proxy')->defaults('sg', 'websitePreview')->name('advertise.design.preview');
                    Route::get('{id}/advertise/sales-checkout/{pkg}', 'ListingController@advertiseSalesCheckout')->name('advertise.sales.checkout');
                    Route::get('ad-banner/{id}', [\App\Http\Controllers\AdBannerController::class, 'edit'])->name('ad.banner.edit');
                    Route::post('ad-banner/{id}', [\App\Http\Controllers\AdBannerController::class, 'update'])->name('ad.banner.update');
                    Route::get('{id}/analytics', 'ListingController@analytics')->name('analytics');
                    Route::get('{id}/response-time', 'ListingController@responseTime')->name('response-time');
                    Route::get('{id}/competitors', 'ListingController@competitors')->name('competitors');
                    Route::get('{id}/business-name', 'ListingController@businessNameForm')->name('business-name');
                    Route::post('{id}/request-name-change', 'ListingController@requestNameChange')->name('request-name-change');
                    Route::get('{id}/business-address', 'ListingController@businessAddressForm')->name('business-address');
                    Route::post('{id}/request-address-change', 'ListingController@requestAddressChange')->name('request-address-change');
                    Route::get('{id}/business-contact', 'ListingController@businessContactForm')->name('business-contact');
                    Route::post('{id}/business-contact', 'ListingController@updateContactDetails')->name('business-contact.update');

                    Route::post('{id}/contact/send-otp', 'ListingController@sendContactOtp')->middleware('throttle:30,1')->name('contact.send-otp');
                    Route::post('{id}/contact/verify-otp', 'ListingController@verifyContactOtp')->middleware('throttle:60,1')->name('contact.verify-otp');
                    Route::get('{id}/serviceable-area', 'ListingController@businessServiceableAreaForm')->name('serviceable-area');
                    Route::post('{id}/serviceable-area', 'ListingController@updateServiceableArea')->name('serviceable-area.update');
                    Route::get('{id}/map-location', 'ListingController@businessMapLocationForm')->name('map-location');
                    Route::post('{id}/map-location', 'ListingController@updateMapLocation')->name('map-location.update');
                    Route::get('{id}/business-timings', 'ListingController@businessTimingsForm')->name('business-timings');
                    Route::post('{id}/business-timings', 'ListingController@updateBusinessTimings')->name('business-timings.update');
                    Route::get('{id}/established', 'ListingController@businessEstablishedForm')->name('established');
                    Route::post('{id}/established', 'ListingController@updateEstablished')->name('established.update');
                    Route::get('{id}/business-categories', 'ListingController@businessCategoriesForm')->name('business-categories');
                    // POST (not GET) and named "lookup", not "search": the
                    // hosting firewall in front of prod blocks the GET
                    // "?q=..." query on a /search path with a 403, while a
                    // small POST body sails through (same as the OTP POST).
                    Route::post('{id}/business-categories/lookup', 'ListingController@searchSubcategories')->name('business-categories.lookup');
                    Route::post('{id}/business-categories', 'ListingController@updateCategories')->name('business-categories.update');
                    Route::get('{id}/business-photos', 'ListingController@businessPhotosForm')->name('business-photos');
                    Route::post('{id}/business-photos', 'ListingController@uploadGalleryPhotos')->name('business-photos.upload');
                    Route::post('{id}/business-photos/{imageId}/cover', 'ListingController@setCoverPhoto')->name('business-photos.cover');
                    Route::post('{id}/business-photos/legacy-remove', 'ListingController@removeLegacyPhoto')->name('business-photos.legacy-remove');
                    Route::post('{id}/business-photos/legacy-cover', 'ListingController@setLegacyCoverPhoto')->name('business-photos.legacy-cover');
                    Route::post('{id}/business-photos/video', 'ListingController@updateVideoLink')->name('business-photos.video');
                    Route::get('{id}/business-catalogue', 'ListingController@businessCatalogueForm')->name('business-catalogue');
                    Route::post('{id}/business-catalogue', 'ListingController@uploadCatalogueImages')->name('business-catalogue.upload');
                    Route::get('{id}/business-website', 'ListingController@businessWebsiteForm')->name('business-website');
                    Route::post('{id}/business-website', 'ListingController@updateWebsite')->name('business-website.update');
                    Route::get('{id}/business-social', 'ListingController@businessSocialForm')->name('business-social');
                    Route::post('{id}/business-social', 'ListingController@updateSocialLinks')->name('business-social.update');
                    Route::get('{id}/ratings-qr', 'ListingController@businessRatingsQrForm')->name('ratings-qr');
                    Route::get('{id}/leads', 'ListingController@leads')->name('leads');
                    Route::post('leads/{enquiry}/star', 'ListingController@leadStar')->name('leads.star');
                    Route::post('leads/{enquiry}/hide', 'ListingController@leadHide')->name('leads.hide');
                    Route::post('leads/{enquiry}/note', 'ListingController@leadNote')->name('leads.note');
                    Route::post('leads/{enquiry}/reminder', 'ListingController@leadReminder')->name('leads.reminder');
                    Route::post('leads/{enquiry}/read', 'ListingController@leadRead')->name('leads.read');
                    Route::get('{id}/import-leads', 'ListingController@importLeadsForm')->name('import-leads');
                    Route::post('{id}/import-leads', 'ListingController@importLeadsUpload')->name('import-leads.upload');
                });
            });

            // Browser push subscription (Web Push / VAPID)
            Route::post('push/subscribe',   'PushSubscriptionController@subscribe')->name('push.subscribe');
            Route::delete('push/unsubscribe', 'PushSubscriptionController@unsubscribe')->name('push.unsubscribe');

            // The bell in the site header.
            Route::get('notifications/feed',       'UserNotificationController@feed')->middleware('throttle:60,1')->name('notifications.feed');
            Route::post('notifications/read-all',  'UserNotificationController@readAll')->name('notifications.read-all');
            Route::post('notifications/{id}/read', 'UserNotificationController@read')->whereNumber('id')->name('notifications.read');
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Guest Chat Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('guest-chat')->name('guest-chat.')->group(function () {
        Route::get('session', 'GuestChatController@sessionStatus')->middleware('throttle:30,1')->name('session');
        Route::get('notifications', 'GuestChatController@notifications')->middleware('throttle:30,1')->name('notifications');
        Route::post('start', 'GuestChatController@start')->middleware('throttle:10,1')->name('start');
        Route::post('verify-otp', 'GuestChatController@verifyOtp')->middleware('throttle:10,1')->name('verify');
        Route::post('send', 'GuestChatController@send')->middleware('throttle:30,1')->name('send');
        Route::get('messages', 'GuestChatController@messages')->middleware('throttle:30,1')->name('messages');
    });


    Route::middleware(['auth', 'verified'])->name('user.')->prefix('dashboard')->group(function () {
        Route::get('guest-chats', 'OwnerChatController@index')->name('guest-chats.index');
        // Must come before guest-chats/{conversation} — that wildcard route
        // was matching "notifications" as a conversation id and 404ing on
        // every poll, since Laravel matches routes in declaration order.
        Route::get('guest-chats/notifications', 'OwnerChatController@notifications')->middleware('throttle:30,1')->name('guest-chats.notifications');
        Route::get('guest-chats/{conversation}', 'OwnerChatController@show')->name('guest-chats.show');
        Route::post('guest-chats/{conversation}/send', 'OwnerChatController@send')->middleware('throttle:30,1')->name('guest-chats.send');
        Route::get('guest-chats/{conversation}/messages', 'OwnerChatController@messages')->middleware('throttle:30,1')->name('guest-chats.messages');
    });


    /*
    |--------------------------------------------------------------------------
    | Common Routes
    |--------------------------------------------------------------------------
    */

    Route::get('search', 'UserController@search')->name('users.search');
    // Sitemap index + section/city child sitemaps. sitemap.xml is the index
    // (that's the URL robots.txt and Search Console already know); the rest
    // are its children so coverage can be diagnosed section-by-section.
    Route::get('sitemap.xml', 'SitemapController@index')->name('sitemap');
    Route::get('sitemap-pages.xml', 'SitemapController@pages')->name('sitemap.pages');
    Route::get('sitemap-listings.xml', 'SitemapController@listingsSitemap')->name('sitemap.listings');
    Route::get('sitemap-city-{city}.xml', 'SitemapController@city')
        ->where('city', '[a-z0-9-]+')->name('sitemap.city');
    // Common alternate index names some tools probe for — send them to the real one.
    Route::redirect('sitemap_index.xml', '/sitemap.xml', 301);
    Route::redirect('sitemap-index.xml', '/sitemap.xml', 301);

Route::post('/set-location-session', function (Illuminate\Http\Request $request) {
        session(['selected_location' => english_location_name($request->location)]);
        return response()->json(['success' => true]);
    })->name('set.location.session');

    Route::post('/set-category-session', function (Illuminate\Http\Request $request) {
        session(['selected_category' => $request->category]);
        return response()->json(['success' => true]);
    })->name('set.category.session');


    /*
    |--------------------------------------------------------------------------
    | IPN Routes
    |--------------------------------------------------------------------------
    */

    // Mobile-app checkout: opened in the app's web-view after POST /api/deposit.
    // Public (token-authed app holds no web session); the random trx guards it.
    Route::get('pay/{trx}', 'DepositController@appCheckout')->name('app.pay');

    Route::get('ipn/flutterwave/{trx_number}/{type}', 'DepositController@flutterwaveIpn')->name('ipn.flutterwave');
    Route::post('ipn/stripe', 'DepositController@stripeIpn')->name('ipn.stripe');
    Route::post('ipn/paytm', 'DepositController@paytmIpn')->name('ipn.paytm');
    Route::post('ipn/paypal', 'DepositController@paypalIpn')->name('ipn.paypal');
    Route::post('ipn/skrill', 'DepositController@skrillIpn')->name('ipn.skrill');
    Route::post('ipn/payeer', 'DepositController@payeerIpn')->name('ipn.payeer');
    Route::post('ipn/paystack', 'DepositController@paystackIpn')->name('ipn.paystack');
    Route::post('ipn/instamojo', 'DepositController@instamojoIpn')->name('ipn.instamojo');
    Route::post('ipn/razorpay', 'DepositController@razorpayIpn')->name('ipn.razorpay');
    // GET: Cashfree's hosted checkout returns the browser here via a plain
    // redirect (not a JS-driven form POST like Razorpay's modal), carrying
    // only ?order_id — cashfreeIpn() re-verifies that server-to-server
    // before trusting it.
    Route::get('ipn/cashfree', 'DepositController@cashfreeIpn')->name('ipn.cashfree');
    Route::post('ipn/vougepay', 'DepositController@vougepayIpn')->name('ipn.vougepay');
    Route::post('ipn/manual', 'DepositController@manualIpn')->name('ipn.manual');


    /*
    |--------------------------------------------------------------------------
    | Frontend Routes
    |--------------------------------------------------------------------------
    */

    Route::redirect('/public/home', '/', 301);
    Route::redirect('/home', '/', 301);
    Route::redirect('/public', '/', 301);
    Route::redirect('/sample-page', '/', 301);
    Route::redirect('/bookings', '/', 301);
    Route::redirect('/my-profile', '/', 301);
    Route::redirect('/hotels', '/listings', 301);
    Route::redirect('/38', '/privacy-policy', 301);
    // The page lives at /terms-and-conditions; old/typo links to the shorter
    // slug used to 404. Send them to the real page instead.
    Route::redirect('/terms-conditions', '/terms-and-conditions', 301);
Route::get('/jaipur/maintenance', function () {
    return redirect()->route('home');
});
    Route::get('/', 'SiteController@index')->name('home');

    Route::get('faq/', 'SiteController@faq')->name('faq');
    Route::get('language/{code}', 'SiteController@changeLang');

    Route::get('blogs', 'SiteController@blogs')->name('blogs');
    Route::get('blog/{slug}', 'SiteController@blogDetails')->name('blog.details');
    Route::post('blog/{slug}/comment', 'SiteController@blogComment')->name('blog.comment')->middleware('throttle:5,1');
Route::get('popular-services', 'SiteController@serviceKeywords')
    ->name('service.keywords');

    Route::get('popular-services/{category}', 'SiteController@serviceKeywordCategory')
    ->name('service.keywords.category');



    Route::get('contact', 'SiteController@contact')->name('contact');
    Route::post('contact', 'SiteController@contactSubmit')->middleware('throttle:10,1')->name('contact.sumbit');

    /*
     * 30/minute, not 10. The limit is keyed by IP, and most Indian mobile
     * networks put a great many subscribers behind one shared address — so ten
     * enquiries a minute is not ten people, it is however many of that
     * carrier's customers happen to be on the site. Real visitors were being
     * refused.
     *
     * Raising it costs nothing now that a repeat of the same enquiry is
     * rejected on its own merits (Enquiry::dedupeKeyFor), so this limit no
     * longer has to be the thing that stops someone submitting twice — it only
     * has to stop actual flooding, which 30 still does.
     */
    Route::post('author-contact', 'SiteController@authorContact')->middleware('throttle:30,1')->name('contact.author');

    Route::get('request-quote', 'SiteController@requestQuoteForm')->name('request-quote');
    Route::post('request-quote', 'SiteController@requestQuoteStore')->middleware('throttle:10,1')->name('request-quote.store');
    Route::post('author-email-contact', 'SiteController@authorEmailContact')
    ->middleware('throttle:30,1')->name('contact.author.email');

    /*
     * The OTP step in front of every enquiry form. Tighter limits than the
     * enquiry endpoints themselves: each send costs a WhatsApp message, so
     * this is the one place worth rate-limiting hard. OtpGate adds a per-number
     * cooldown and a per-session budget on top of these per-IP caps.
     */
    Route::post('enquiry-otp/send', 'EnquiryOtpController@send')
        ->middleware('throttle:120,1')->name('enquiry.otp.send');
    Route::post('enquiry-otp/verify', 'EnquiryOtpController@verify')
        ->middleware('throttle:15,1')->name('enquiry.otp.verify');
    Route::post('listing-click-track', 'SiteController@listingClickTrack')->middleware('throttle:60,1')->name('listing.click.track');

    Route::prefix('track')->middleware('throttle:120,1')->group(function () {
        Route::post('page-view/open', 'TrackingController@pageViewOpen')->name('track.page.open');
        Route::post('page-view/close', 'TrackingController@pageViewClose')->name('track.page.close');
        Route::post('event', 'TrackingController@event')->name('track.event');
    });

    Route::get('pricing', 'SiteController@pricing')->name('pricing');

    Route::get('listings', 'SiteController@listings')->name('listings');

    Route::get('listings/search', 'SiteController@search')->name('listings.search');
    // 'pay/{trx}' is already taken by the app checkout, so sales links live here.
    // Both shapes work, because WhatsApp template builders differ on which
    // dynamic-URL format they accept: /order-pay/<token> and /order-pay?t=<token>
    Route::get('order-pay',               [\App\Http\Controllers\SalesOrderController::class, 'showByQuery'])->name('sales.order.pay.q');
    Route::get('p/{token}',               [\App\Http\Controllers\SalesOrderController::class, 'show'])->name('sales.order.pay.short');
    Route::get('order-pay/{token}',       [\App\Http\Controllers\SalesOrderController::class, 'show'])->name('sales.order.pay');
    Route::post('order-pay/{token}/start',   [\App\Http\Controllers\SalesOrderController::class, 'createPayment'])->name('sales.order.start');
    Route::post('order-pay/{token}/confirm', [\App\Http\Controllers\SalesOrderController::class, 'confirm'])->name('sales.order.confirm');
    Route::get('order-pay/{token}/invoice',  [\App\Http\Controllers\SalesOrderController::class, 'invoice'])->name('sales.order.invoice');
    Route::get('order-pay/{token}/invoice/resend', [\App\Http\Controllers\SalesOrderController::class, 'resendInvoice'])->name('sales.order.invoice.resend');

    Route::get('category/search', 'SiteController@categorySearch')->name('category.search');

 Route::get('listings/subcategory/{slug}', 'SiteController@listingsSubcategory')->name('listings.subcategory');
    Route::get('listings/location/{slug}', 'SiteController@listingsLocation')->name('listings.location');
    Route::get('listings/author/{id}', 'SiteController@listingsAuthor')->name('listings.author');
    // Same reasoning as the city/category catch-all below: an uncached detail
    // page is expensive, and there is one per listing for a crawler to walk.
    Route::get('listings/{category}/{slug}', 'SiteController@listingsDetails')
        ->middleware('throttle:60,1')
        ->name('listings.details');

    // $slug here is a *category* slug (see index.blade.php's
    // route('listings.category', $categoryItem->slug) links) — this is a
    // short "browse this category" URL that used to always redirect to
    // /jaipur/{slug} no matter which city the visitor was actually
    // browsing. Every visitor outside Jaipur clicking a category link from
    // the homepage landed on the Jaipur listings instead of their own city
    // — which is also what was showing up as mismatched-city "Redirect
    // error" entries in Search Console (a crawler has no session, so it
    // always fell through to the same hardcoded default anyway, but a real
    // visitor's browser doesn't). Now it resolves the same way the rest of
    // the site resolves "current city" (session, then a sane default), and
    // uses a 302: the target depends on session, so it must never be
    // permanently cached against one city the way a 301 would be.
    Route::get('listings/{slug}', function (\Illuminate\Http\Request $request, $slug) {
        $city = $request->query('location_name')
            ?: session('selected_location')
            ?: session('detected_city')
            ?: session('user_location')
            ?: 'jaipur';

        return redirect()->to('/' . \Illuminate\Support\Str::slug($city) . '/' . $slug, 302);
    })->name('listings.category');

    /*
    |--------------------------------------------------------------------------
    | City Page
    |--------------------------------------------------------------------------
    | Example: /location/delhi
    | Is route ko {location}/{slug} se upar rakha hai.
    */
    Route::get('/location/{location}', 'SiteController@index')
        ->name('home.location');

    // ── SB Sales (formerly Sales Genio) — before {location}/{slug} catch-all ──
    // 308 keeps the method and body, so a form left open on the old URL still submits.
    Route::any('sales_genio/{rest?}', fn (\Illuminate\Http\Request $r, $rest = '') =>
        redirect('/sb-sales' . ($rest !== '' ? '/' . $rest : '') . ($r->getQueryString() ? '?' . $r->getQueryString() : ''), 308)
    )->where('rest', '.*');

    Route::prefix('sb-sales')->name('sales_genio.')->group(function () {
        Route::get('/',         [\App\Http\Controllers\SalesGenioController::class, 'showLogin'])->name('login');
        Route::post('/login',   [\App\Http\Controllers\SalesGenioController::class, 'login'])->name('login.post');
        Route::get('/otp',      [\App\Http\Controllers\SalesGenioController::class, 'showOtp'])->name('otp');
        Route::post('/otp',     [\App\Http\Controllers\SalesGenioController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('otp.verify');

        Route::get('/login-otp',  [\App\Http\Controllers\SalesGenioController::class, 'showOtpLogin'])->name('otp.login');
        Route::post('/login-otp', [\App\Http\Controllers\SalesGenioController::class, 'sendLoginOtp'])->middleware('throttle:5,1')->name('otp.login.send');

        Route::get('/forgot-password',         [\App\Http\Controllers\SalesGenioController::class, 'showForgotPassword'])->name('password.forgot');
        Route::post('/forgot-password',        [\App\Http\Controllers\SalesGenioController::class, 'sendResetOtp'])->middleware('throttle:5,1')->name('password.forgot.send');
        Route::get('/forgot-password/reset',   [\App\Http\Controllers\SalesGenioController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/forgot-password/reset',  [\App\Http\Controllers\SalesGenioController::class, 'resetPassword'])->middleware('throttle:10,1')->name('password.reset.save');

        Route::middleware(['sales.session', 'sales.auth', 'sales.touch', 'sales.geo'])->group(function () {
            Route::get('/dashboard',        [\App\Http\Controllers\SalesGenioController::class, 'dashboard'])->name('dashboard');
            // GPS beacon from every panel page (SalesGeoBeacon).
            Route::post('/location',        [\App\Http\Controllers\SalesLocationController::class, 'store'])->middleware('throttle:30,1')->name('location');
            Route::post('/city',            [\App\Http\Controllers\SalesGenioController::class, 'switchCity'])->name('city');
            Route::get('/city/clear',       [\App\Http\Controllers\SalesGenioController::class, 'clearCity'])->name('city.clear');
            Route::get('/search-suggest',   [\App\Http\Controllers\SalesGenioController::class, 'searchSuggest'])->name('search.suggest');
            Route::get('/leads/{tab}',      [\App\Http\Controllers\SalesGenioController::class, 'leads'])->name('leads');
            Route::get('/reports',          [\App\Http\Controllers\SalesGenioController::class, 'reports'])->name('reports');
            Route::get('/create-business',        [\App\Http\Controllers\SalesGenioController::class, 'createBusiness'])->name('create.business');
            Route::post('/create-business/check', [\App\Http\Controllers\SalesGenioController::class, 'checkBusiness'])->name('create.business.check');
            Route::post('/create-business',       [\App\Http\Controllers\SalesGenioController::class, 'storeBusiness'])->name('create.business.store');
            Route::get('/area-search',            [\App\Http\Controllers\SalesGenioController::class, 'areaSearch'])->name('area.search');
            Route::get('/business/{id}',                  [\App\Http\Controllers\SalesGenioController::class, 'businessProfile'])->name('business.profile');
            Route::get('/business/{id}/address',          [\App\Http\Controllers\SalesGenioController::class, 'editAddress'])->name('business.address');
            Route::post('/business/{id}/address',         [\App\Http\Controllers\SalesGenioController::class, 'updateAddress'])->name('business.address.update');
            Route::get('/business/{id}/contact',          [\App\Http\Controllers\SalesGenioController::class, 'editContact'])->name('business.contact');
            Route::post('/business/{id}/contact',         [\App\Http\Controllers\SalesGenioController::class, 'updateContact'])->name('business.contact.update');
            Route::get('/business/{id}/categories',       [\App\Http\Controllers\SalesGenioController::class, 'editCategories'])->name('business.categories');
            Route::post('/business/{id}/categories',      [\App\Http\Controllers\SalesGenioController::class, 'updateCategories'])->name('business.categories.update');
            Route::get('/business/{id}/map',              [\App\Http\Controllers\SalesGenioController::class, 'editMap'])->name('business.map');
            Route::post('/business/{id}/map',             [\App\Http\Controllers\SalesGenioController::class, 'updateMap'])->name('business.map.update');
            Route::get('/business/{id}/awards',           [\App\Http\Controllers\SalesGenioController::class, 'editAwards'])->name('business.awards');
            Route::post('/business/{id}/awards',          [\App\Http\Controllers\SalesGenioController::class, 'updateAwards'])->name('business.awards.update');
            Route::get('/business/{id}/certificates',     [\App\Http\Controllers\SalesGenioController::class, 'editCertificates'])->name('business.certificates');
            Route::post('/business/{id}/certificates',    [\App\Http\Controllers\SalesGenioController::class, 'updateCertificates'])->name('business.certificates.update');
            Route::post('/business/{id}/disposition',     [\App\Http\Controllers\SalesGenioController::class, 'saveDisposition'])->name('business.disposition');
            Route::get('/business/{id}/campaign',         [\App\Http\Controllers\SalesGenioController::class, 'manageCampaign'])->name('business.campaign');
            Route::post('/business/{id}/campaign/skip',   [\App\Http\Controllers\SalesGenioController::class, 'skipCampaignOptional'])->name('business.campaign.skip');
            Route::get('/business/{id}/campaign/{pkg}/template', [\App\Http\Controllers\SalesGenioController::class, 'websiteTemplates'])->name('business.campaign.template');
            Route::get('/business/{id}/website-preview/{template}', [\App\Http\Controllers\SalesGenioController::class, 'websitePreview'])->name('business.website.preview');
            Route::get('/business/{id}/campaign/{pkg}/areas',     [\App\Http\Controllers\SalesGenioController::class, 'campaignAreas'])->name('business.campaign.areas');
            Route::post('/business/{id}/campaign/{pkg}/areas',    [\App\Http\Controllers\SalesGenioController::class, 'saveCampaignAreas'])->name('business.campaign.areas.save');
            Route::get('/business/{id}/campaign/{pkg}/position',  [\App\Http\Controllers\SalesGenioController::class, 'campaignPosition'])->name('business.campaign.position');
            Route::post('/business/{id}/campaign/{pkg}/position', [\App\Http\Controllers\SalesGenioController::class, 'saveCampaignPosition'])->name('business.campaign.position.save');
            Route::get('/business/{id}/campaign/{pkg}/review',    [\App\Http\Controllers\SalesGenioController::class, 'campaignReview'])->name('business.campaign.review');
            Route::post('/business/{id}/campaign/{pkg}/review',   [\App\Http\Controllers\SalesGenioController::class, 'confirmCampaign'])->name('business.campaign.confirm');
            Route::get('/carts',                        [\App\Http\Controllers\SalesGenioController::class, 'carts'])->name('carts');
            Route::get('/business/{id}/cart',            [\App\Http\Controllers\SalesGenioController::class, 'cart'])->name('business.cart');
            Route::post('/business/{id}/cart/add',       [\App\Http\Controllers\SalesGenioController::class, 'quickAddCart'])->name('business.cart.add');
            Route::get('/business/{id}/cart/checkout',   [\App\Http\Controllers\SalesGenioController::class, 'cartCheckout'])->name('business.cart.checkout');
            Route::get('/business/{id}/cart/gst',        [\App\Http\Controllers\SalesGenioController::class, 'gstDetails'])->name('business.cart.gst');
            Route::post('/business/{id}/cart/order',     [\App\Http\Controllers\SalesGenioController::class, 'generateOrder'])->name('business.cart.order');
            Route::post('/business/{id}/cart/discount',  [\App\Http\Controllers\SalesGenioController::class, 'applyDiscount'])->name('business.cart.discount');
            Route::post('/business/{id}/cart/discount/remove', [\App\Http\Controllers\SalesGenioController::class, 'removeDiscount'])->name('business.cart.discount.remove');
            Route::get('/business/{id}/order/{order}',   [\App\Http\Controllers\SalesGenioController::class, 'showOrder'])->name('business.order');
            Route::post('/business/{id}/order/{order}/whatsapp', [\App\Http\Controllers\SalesGenioController::class, 'sendOrderWhatsapp'])->name('business.order.whatsapp');
            Route::post('/business/{id}/order/{order}/close',    [\App\Http\Controllers\SalesGenioController::class, 'dealClose'])->name('business.order.close');
            Route::post('/business/{id}/order/{order}/invoice-mail', [\App\Http\Controllers\SalesGenioController::class, 'mailOrderInvoice'])->name('business.order.invoice.mail');
            Route::post('/business/{id}/cart/clear',     [\App\Http\Controllers\SalesGenioController::class, 'clearCart'])->name('business.cart.clear');
            Route::post('/business/{id}/campaign/cart',   [\App\Http\Controllers\SalesGenioController::class, 'addCampaignCart'])->name('business.campaign.cart');
            Route::post('/business/{id}/campaign/cart/remove', [\App\Http\Controllers\SalesGenioController::class, 'removeCampaignCart'])->name('business.campaign.cart.remove');
            Route::get('/business/{id}/language',         [\App\Http\Controllers\SalesGenioController::class, 'editLanguage'])->name('business.language');
            Route::post('/business/{id}/language',        [\App\Http\Controllers\SalesGenioController::class, 'updateLanguage'])->name('business.language.update');
            Route::get('/business/{id}/name',             [\App\Http\Controllers\SalesGenioController::class, 'editName'])->name('business.name');
            Route::post('/business/{id}/name',            [\App\Http\Controllers\SalesGenioController::class, 'updateName'])->name('business.name.update');
            Route::get('/business/{id}/kyc',              [\App\Http\Controllers\SalesGenioController::class, 'editKyc'])->name('business.kyc');
            Route::post('/business/{id}/kyc',             [\App\Http\Controllers\SalesGenioController::class, 'uploadKyc'])->name('business.kyc.upload');
            Route::post('/business/{id}/kyc/number',      [\App\Http\Controllers\SalesGenioController::class, 'saveKycNumber'])->name('business.kyc.number');
            Route::delete('/business/{id}/kyc/{doc}',     [\App\Http\Controllers\SalesGenioController::class, 'deleteKyc'])->name('business.kyc.delete');
            Route::get('/business/{id}/social',           [\App\Http\Controllers\SalesGenioController::class, 'editSocial'])->name('business.social');
            Route::post('/business/{id}/social',          [\App\Http\Controllers\SalesGenioController::class, 'updateSocial'])->name('business.social.update');
            Route::get('/business/{id}/website',          [\App\Http\Controllers\SalesGenioController::class, 'editWebsite'])->name('business.website');
            Route::post('/business/{id}/website',         [\App\Http\Controllers\SalesGenioController::class, 'updateWebsite'])->name('business.website.update');
            Route::get('/business/{id}/timings',          [\App\Http\Controllers\SalesGenioController::class, 'editTimings'])->name('business.timings');
            Route::post('/business/{id}/timings',         [\App\Http\Controllers\SalesGenioController::class, 'updateTimings'])->name('business.timings.update');
            Route::get('/business/{id}/establishment',    [\App\Http\Controllers\SalesGenioController::class, 'editEstablishment'])->name('business.establishment');
            Route::post('/business/{id}/establishment',   [\App\Http\Controllers\SalesGenioController::class, 'updateEstablishment'])->name('business.establishment.update');
            Route::get('/subcategories-by-category',      [\App\Http\Controllers\SalesGenioController::class, 'subcategoriesByCategory'])->name('subcategories.by.category');

            Route::get('/profile',                 [\App\Http\Controllers\SalesGenioController::class, 'profile'])->name('profile');
            Route::post('/profile',                [\App\Http\Controllers\SalesGenioController::class, 'updateProfile'])->name('profile.update');
            Route::post('/profile/avatar',         [\App\Http\Controllers\SalesGenioController::class, 'updateAvatar'])->name('profile.avatar');
            Route::post('/profile/avatar/remove',  [\App\Http\Controllers\SalesGenioController::class, 'removeAvatar'])->name('profile.avatar.remove');
            Route::post('/profile/password',       [\App\Http\Controllers\SalesGenioController::class, 'updatePassword'])->name('profile.password');
            Route::get('/business/{id}/photos',           [\App\Http\Controllers\SalesGenioController::class, 'photoGallery'])->name('business.photos');
            Route::post('/business/{id}/photos',          [\App\Http\Controllers\SalesGenioController::class, 'uploadPhotos'])->name('business.photos.upload');
            Route::delete('/business/{id}/photos',        [\App\Http\Controllers\SalesGenioController::class, 'deletePhoto'])->name('business.photos.delete');
            Route::post('/business/{id}/video-poster',    [\App\Http\Controllers\SalesGenioController::class, 'videoPoster'])->name('business.video.poster');
            Route::post('/business/{id}/photos/cover',    [\App\Http\Controllers\SalesGenioController::class, 'setCoverPhoto'])->name('business.photos.cover');
            Route::get('/business/{id}/catalogue',        [\App\Http\Controllers\SalesGenioController::class, 'catalogue'])->name('business.catalogue');
            Route::post('/business/{id}/catalogue',       [\App\Http\Controllers\SalesGenioController::class, 'uploadCatalogue'])->name('business.catalogue.upload');
            Route::post('/business/{id}/catalogue/{image}/delete', [\App\Http\Controllers\SalesGenioController::class, 'deleteCatalogue'])->name('business.catalogue.delete');

            // GET hits on the POST-only endpoints (typed/refreshed directly)
            // bounce to the form — and when unauthenticated, sales.auth sends
            // them to login instead of a 404.
            Route::get('/create-business/check', fn () => redirect()->route('sales_genio.create.business'));
            Route::post('/logout',          [\App\Http\Controllers\SalesGenioController::class, 'logout'])->name('logout');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Category With Location
    |--------------------------------------------------------------------------
    | Example: /jaipur/packers-and-movers
    */

/*
 * Throttled because these are the expensive pages — an uncached city/category
 * render is ~27 queries, and the crawlable space is 60 cities x 5,535
 * subcategories, so a crawler sweeping it holds every PHP worker and drains
 * the MySQL connection pool until the account is refused new work and even
 * admin requests come back as a bare 403.
 *
 * The limit sits well above human browsing (a person never opens 60 distinct
 * pages in a minute) and only ever applies to a cache MISS: CachePublicPages
 * runs in the `web` group, ahead of route middleware, so a cached page is
 * returned before the throttle is consulted. A crawler that trips it gets a
 * cheap 429, which is also the response that tells it to slow down — unlike
 * the 504s and 403s it was provoking before.
 */
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/{city}/{categoryArea}', 'ListingController@cityCategoryArea')
        ->where('city', '^(?!admin|user|dashboard|login|contact|blogs|blog|listings|category|search|free-listing|location|SbSocial|popular-services$).*$')
        ->name('listings.city.category.area');

    Route::get('{location}/{slug}', 'SiteController@listingsCategoryLocation')
        ->name('listings.category.location');
    Route::get('/{category}/{keyword}', 'SiteController@serviceKeywordDetails')
        ->name('service.keywords.details');
});
    Route::post('subscribe', 'SiteController@subscribe')->middleware('throttle:10,1')->name('subscribe');

    Route::get('search-locations', 'SiteController@searchLocations')->name('locations.search');

    Route::get('/free-listing', 'SiteController@freelisting')->name('free.listing');
    Route::post('/free-listing-store', 'SiteController@freeListingStore')->middleware('throttle:10,1')->name('free.listing.store');

    // Same Gemini-backed content generator the admin "Add Listing" form
    // uses for Meta Title/Keywords/Description + Listing Details — reused
    // here so a listing self-submitted from /free-listing gets exactly the
    // same auto-filled content, under the same conditions (name + city +
    // category present), instead of duplicating that logic. This route
    // exists only because Admin\ListingController::aiGenerate() otherwise
    // sits behind the 'admin' guard: a regular signed-in website user is
    // never an admin, so listing_form.blade.php's fetch to that route used
    // to get silently redirected to the admin login page instead of JSON —
    // response.ok still read true after the redirect completed, but
    // response.json() then threw on the login page's HTML, which the catch
    // block swallowed, leaving every meta/details field blank. 'auth' (the
    // default 'web' guard, same one freelisting() itself requires) plus a
    // throttle keeps this from being hit by a logged-out script while still
    // costing nothing extra to reuse the identical controller method.
    Route::post('/free-listing-ai-generate', [\App\Http\Controllers\Admin\ListingController::class, 'aiGenerate'])
        ->middleware(['auth', 'throttle:15,1'])
        ->name('free.listing.ai.generate');


    /*
    |--------------------------------------------------------------------------
    | Review Routes
    |--------------------------------------------------------------------------
    */

    Route::post('review', 'ListingController@review')->middleware('throttle:10,1')->name('review');
    Route::post('review-verify-otp', 'ListingController@verifyReviewOtp')->middleware('throttle:5,1')->name('review.verify.otp');
    Route::put('review-update/{id}', 'ListingController@updateReview')->name('review.update');
    Route::post('review-edit-otp', 'ListingController@sendEditReviewOtp')->middleware('throttle:10,1')->name('review.edit.otp');
    Route::post('review-edit-verify-otp', 'ListingController@verifyEditReviewOtp')->middleware('throttle:5,1')->name('review.edit.verify.otp');
Route::delete('review-delete/{id}', 'ListingController@deleteReview')->name('review.delete');

    Route::post('rating/like', 'RatingInteractionController@like')->name('rating.like');
Route::post('rating/comment', 'RatingInteractionController@comment')->name('rating.comment');
Route::delete('rating/comment/{id}', 'RatingInteractionController@deleteComment')->name('rating.comment.delete');

    Route::post('save-user-location', 'SiteController@saveUserLocation')->middleware('throttle:30,1')->name('save.user.location');
    Route::post('ajax/popular-categories', 'SiteController@ajaxPopularCategories')
    ->name('ajax.popular.categories');
Route::get('geocode-address', 'SiteController@geocodeAddress')->name('geocode.address');

    /*
    |--------------------------------------------------------------------------
    | Useful Links
    |--------------------------------------------------------------------------
    | Example: /about-us, /privacy-policy
    | Isko sabse last me rakha hai.
    */
    Route::get('/delete-account', 'SiteController@deleteAccount')->name('delete.account');
    Route::post('/delete-account', 'SiteController@deleteAccountSubmit')->middleware('throttle:5,1')->name('delete.account.submit');

    // Bridge call (number masking) — pilot: Jaipur / IT Consultants listings only.
    // No throttle: already gated behind auth + the category/location
    // eligibility check in the controller, and Exotel's own trial whitelist
    // bounds the blast radius further. A per-minute cap here only got in the
    // way of repeated pilot testing.
    Route::post('/bridge-call', 'SiteController@bridgeCall')->middleware(['auth'])->name('bridge.call');

    // Option B (masked outbound): customer dials the virtual number themselves.
    // 'prepare' records the caller->vendor mapping; Exotel then fetches the
    // destination from the public 'voice' webhook when the ExoPhone rings.
    Route::post('/bridge-call/prepare', 'SiteController@prepareMaskedCall')->middleware(['auth'])->name('bridge.call.prepare');


Route::get('/{slug}', 'SiteController@handleSingleSlug')
    ->name('single.slug');


});

use Illuminate\Support\Facades\Http;

// Areas for a pincode in admin panel. The upstream postal API is missing plenty of real
// pincodes, so this also offers the areas our own listings already use for it
// — see Admin\PincodeController.
// Registered outside the admin group, so it does not pick up that group's
// `Admin\` controller namespace — hence the fully qualified class.
Route::get('/admin/pincode/{pin}', [\App\Http\Controllers\Admin\PincodeController::class, 'show'])
    ->where('pin', '[0-9]{4,10}')
    ->middleware(['web', 'admin'])
    ->name('admin.pincode.fetch');



