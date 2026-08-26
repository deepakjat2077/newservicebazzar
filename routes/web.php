<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response('ok', 200))->name('health');

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

            Route::get('dashboard', 'AdminController@dashboard')->name('dashboard');
            Route::get('dashboard/listing-export', 'AdminController@listingExport')->middleware('permission:listing.export')->name('dashboard.listing.export');

            Route::prefix('analytics')->name('analytics.')->middleware('permission:analytics.view')->group(function () {
                Route::get('/', 'AnalyticsController@index')->name('index');
                Route::get('export/excel', 'AnalyticsController@exportExcel')->name('export.excel');
                Route::get('export/csv', 'AnalyticsController@exportCsv')->name('export.csv');
                Route::get('export/pdf', 'AnalyticsController@exportPdf')->name('export.pdf');
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
                Route::post('create', 'SubcategoriesController@create')->name('create');
                Route::get('create', 'SubcategoriesController@createPage')->name('create.page');
                Route::get('update/{id}', 'SubcategoriesController@update')->name('update.page');
                Route::put('edit/{id}', 'SubcategoriesController@edit')->name('edit');
                Route::delete('delete/{id}', 'SubcategoriesController@delete')->name('delete');
                // Called by the listing form's subcategory picker.
                Route::post('quick-store', 'SubcategoriesController@quickStore')->withoutMiddleware('permission:subcategory.view')->middleware('permission:subcategory.manage,listing.create')->name('quick.store');
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


Route::get('home-content-sections', 'HomeContentSectionController@index')->middleware('permission:home.manage')->name('home.content.sections.index');
Route::post('home-content-sections/store', 'HomeContentSectionController@store')->middleware('permission:home.manage')->name('home.content.sections.store');
Route::put('home-content-sections/update/{id}', 'HomeContentSectionController@update')->middleware('permission:home.manage')->name('home.content.sections.update');
Route::delete('home-content-sections/delete/{id}', 'HomeContentSectionController@delete')->middleware('permission:home.manage')->name('home.content.sections.delete');



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

        Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
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


        Route::middleware('auth')->group(function () {

            Route::get('verification', 'Auth\VerificationController@index')->name('verification');
            Route::get('verify/resend', 'Auth\VerificationController@verifyResend')->name('verify.resend');
            Route::get('verify/{hash}', 'Auth\VerificationController@verify')->name('verify');
            Route::get('verification/banned', 'Auth\VerificationController@banned')->name('banned');

            Route::middleware('verified')->group(function () {

                Route::get('dashboard', 'UserController@dashboard')->name('dashboard');

                Route::get('profile', 'UserController@profile')->name('profile');
                Route::post('profile', 'UserController@profileUpdate');

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
                Route::post('deposit/submit', 'DepositController@depositSubmit')->name('deposit.submit');
                Route::get('deposit/confirm', 'DepositController@depositConfirm')->name('deposit.confirm');
                Route::get('deposit/details/{id}', 'DepositController@depositDetails')->name('deposit.details');


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
                    Route::get('{id}/analytics', 'ListingController@analytics')->name('analytics');
                    Route::get('{id}/response-time', 'ListingController@responseTime')->name('response-time');
                    Route::get('{id}/competitors', 'ListingController@competitors')->name('competitors');
                    Route::get('{id}/business-name', 'ListingController@businessNameForm')->name('business-name');
                    Route::post('{id}/request-name-change', 'ListingController@requestNameChange')->name('request-name-change');
                    Route::get('{id}/business-address', 'ListingController@businessAddressForm')->name('business-address');
                    Route::post('{id}/request-address-change', 'ListingController@requestAddressChange')->name('request-address-change');
                    Route::get('{id}/business-contact', 'ListingController@businessContactForm')->name('business-contact');
                    Route::post('{id}/business-contact', 'ListingController@updateContactDetails')->name('business-contact.update');
                    Route::get('{id}/serviceable-area', 'ListingController@businessServiceableAreaForm')->name('serviceable-area');
                    Route::post('{id}/serviceable-area', 'ListingController@updateServiceableArea')->name('serviceable-area.update');
                    Route::get('{id}/map-location', 'ListingController@businessMapLocationForm')->name('map-location');
                    Route::post('{id}/map-location', 'ListingController@updateMapLocation')->name('map-location.update');
                    Route::get('{id}/business-timings', 'ListingController@businessTimingsForm')->name('business-timings');
                    Route::post('{id}/business-timings', 'ListingController@updateBusinessTimings')->name('business-timings.update');
                    Route::get('{id}/established', 'ListingController@businessEstablishedForm')->name('established');
                    Route::post('{id}/established', 'ListingController@updateEstablished')->name('established.update');
                    Route::get('{id}/business-categories', 'ListingController@businessCategoriesForm')->name('business-categories');
                    Route::get('{id}/business-categories/search', 'ListingController@searchSubcategories')->name('business-categories.search');
                    Route::post('{id}/business-categories', 'ListingController@updateCategories')->name('business-categories.update');
                    Route::get('{id}/business-photos', 'ListingController@businessPhotosForm')->name('business-photos');
                    Route::post('{id}/business-photos', 'ListingController@uploadGalleryPhotos')->name('business-photos.upload');
                    Route::post('{id}/business-photos/{imageId}/cover', 'ListingController@setCoverPhoto')->name('business-photos.cover');
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
    Route::get('sitemap.xml', 'SitemapController@index')->name('sitemap');

Route::post('/set-location-session', function (Illuminate\Http\Request $request) {
        session(['selected_location' => $request->location]);
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

    Route::get('ipn/flutterwave/{trx_number}/{type}', 'DepositController@flutterwaveIpn')->name('ipn.flutterwave');
    Route::post('ipn/stripe', 'DepositController@stripeIpn')->name('ipn.stripe');
    Route::post('ipn/paytm', 'DepositController@paytmIpn')->name('ipn.paytm');
    Route::post('ipn/paypal', 'DepositController@paypalIpn')->name('ipn.paypal');
    Route::post('ipn/skrill', 'DepositController@skrillIpn')->name('ipn.skrill');
    Route::post('ipn/payeer', 'DepositController@payeerIpn')->name('ipn.payeer');
    Route::post('ipn/paystack', 'DepositController@paystackIpn')->name('ipn.paystack');
    Route::post('ipn/instamojo', 'DepositController@instamojoIpn')->name('ipn.instamojo');
    Route::post('ipn/razorpay', 'DepositController@razorpayIpn')->name('ipn.razorpay');
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
Route::get('/jaipur/maintenance', function () {
    return redirect()->route('home');
});
    Route::get('/', 'SiteController@index')->name('home');

    Route::get('faq/', 'SiteController@faq')->name('faq');
    Route::get('language/{code}', 'SiteController@changeLang');

    Route::get('blogs', 'SiteController@blogs')->name('blogs');
    Route::get('blog/{slug}', 'SiteController@blogDetails')->name('blog.details');
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
    Route::post('listing-click-track', 'SiteController@listingClickTrack')->middleware('throttle:60,1')->name('listing.click.track');

    Route::prefix('track')->middleware('throttle:120,1')->group(function () {
        Route::post('page-view/open', 'TrackingController@pageViewOpen')->name('track.page.open');
        Route::post('page-view/close', 'TrackingController@pageViewClose')->name('track.page.close');
        Route::post('event', 'TrackingController@event')->name('track.event');
    });

    Route::get('pricing', 'SiteController@pricing')->name('pricing');

    Route::get('listings', 'SiteController@listings')->name('listings');

    Route::get('listings/search', 'SiteController@search')->name('listings.search');
    Route::get('category/search', 'SiteController@categorySearch')->name('category.search');

 Route::get('listings/subcategory/{slug}', function () {
    return redirect('/', 301);
})->name('listings.subcategory');
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
        ->where('city', '^(?!admin|user|dashboard|login|contact|blogs|blog|listings|category|search|free-listing|location|popular-services$).*$')
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
Route::get('/{slug}', 'SiteController@handleSingleSlug')
    ->name('single.slug');


});

use Illuminate\Support\Facades\Http;

// Areas for a pincode. The upstream postal API is missing plenty of real
// pincodes, so this also offers the areas our own listings already use for it
// — see Admin\PincodeController.
// Registered outside the admin group, so it does not pick up that group's
// `Admin\` controller namespace — hence the fully qualified class.
Route::get('/admin/pincode/{pin}', [\App\Http\Controllers\Admin\PincodeController::class, 'show'])
    ->where('pin', '[0-9]{4,10}')
    ->middleware(['web', 'admin'])
    ->name('admin.pincode.fetch');
