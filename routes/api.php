<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\BrowseController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\BusinessEditController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\WhatsappApiController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->group(function () {

    // Same credentials as /admin — same admins table, same username field.
    // Throttled per username+IP inside the controller.
    Route::post('login', [AdminAuthController::class, 'login'])->name('api.admin.login');

    Route::middleware('admin.api')->group(function () {
        Route::get('me', [AdminAuthController::class, 'me'])->name('api.admin.me');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('api.admin.logout');
    });
});


Route::prefix('auth')->group(function () {

    // Login screen calls this first to know which buttons to show.
    Route::get('config', [UserAuthController::class, 'config']);

    // WhatsApp OTP — send / resend / verify.
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('otp/send',   [UserAuthController::class, 'sendOtp']);
        Route::post('otp/resend', [UserAuthController::class, 'resendOtp']);
    });
    Route::post('otp/verify', [UserAuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1');

    // "Continue with" — the app passes the provider access token its native
    // SDK already obtained.
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('social/google',   [UserAuthController::class, 'google']);
        Route::post('social/facebook', [UserAuthController::class, 'facebook']);
    });

    // Signed-in endpoints.
    Route::middleware('user.api')->group(function () {
        Route::get('me',      [UserAuthController::class, 'me']);
        Route::post('logout', [UserAuthController::class, 'logout']);
    });
});


Route::prefix('profile')->middleware('user.api')->group(function () {

    // Personal details (view + edit).
    Route::get('/',  [ProfileController::class, 'show']);
    Route::post('/', [ProfileController::class, 'update']);

    // Addresses.
    Route::get('addresses',            [ProfileController::class, 'addresses']);
    Route::post('addresses',           [ProfileController::class, 'storeAddress']);
    Route::delete('addresses/{address}', [ProfileController::class, 'destroyAddress'])->whereNumber('address');

    // Favorites — the wizard grid, its per-category business list, and toggles.
    Route::get('favorite-categories',          [ProfileController::class, 'favoriteCategories']);
    Route::post('favorite-categories/toggle',  [ProfileController::class, 'toggleFavoriteCategory']);
    Route::get('favorite-categories/{key}/listings', [ProfileController::class, 'categoryListings']);

    // Favorite (liked) businesses.
    Route::get('favorite-listings',         [ProfileController::class, 'favoriteListings']);
    Route::post('favorite-listings/toggle', [ProfileController::class, 'toggleFavoriteListing']);

    // Reviews this user has given.
    Route::get('ratings', [ProfileController::class, 'ratings']);
});

Route::middleware('user.api')->group(function () {

    Route::get('home',       [BrowseController::class, 'home']);
    Route::get('categories', [BrowseController::class, 'categories']);
    Route::get('categories/find', [BrowseController::class, 'searchCategories']);
    Route::get('categories/find/{term}', [BrowseController::class, 'searchCategories']);
    Route::get('categories/{slug}/subcategories', [BrowseController::class, 'subcategories']);
    Route::get('locations/find',  [BrowseController::class, 'searchLocations']);
    Route::get('locations/find/{term}', [BrowseController::class, 'searchLocations']);

    // Backward compatibility - keep old search routes too
    Route::get('search', [BrowseController::class, 'search']);
    Route::get('search/{term}', [BrowseController::class, 'search']);

    // New routes - not blocked by WAF
    Route::get('find', [BrowseController::class, 'search']);
    Route::get('find/{term}', [BrowseController::class, 'search']);
    Route::get('find/{location}/{term}', [BrowseController::class, 'search']);

    Route::get('listings/{slug}', [BrowseController::class, 'detail']);
    Route::post('listings/{id}/review',  [BrowseController::class, 'submitReview'])->whereNumber('id');
    Route::post('listings/{id}/enquiry', [BrowseController::class, 'enquiry'])->whereNumber('id');
    Route::post('listings/{id}/click',   [BrowseController::class, 'trackClick'])->whereNumber('id');

    Route::post('reviews/{id}/like',    [BrowseController::class, 'likeReview'])->whereNumber('id');
    Route::post('reviews/{id}/comment', [BrowseController::class, 'commentReview'])->whereNumber('id');
    Route::delete('reviews/comments/{id}', [BrowseController::class, 'deleteComment'])->whereNumber('id');

    // News feed (= blog posts) and premium plans.
    Route::get('news',          [ContentController::class, 'news']);
    Route::get('news/{slug}',   [ContentController::class, 'newsDetail']);
    Route::get('plans',         [ContentController::class, 'plans']);

    // Buying a plan through the admin-enabled gateways.
    Route::get('payment-methods',       [PaymentController::class, 'methods']);
    Route::post('deposit',              [PaymentController::class, 'deposit']);
    Route::get('deposit/{trx}/status',  [PaymentController::class, 'status']);
});


Route::prefix('account')->middleware('user.api')->group(function () {

    Route::get('devices',            [AccountController::class, 'devices']);
    Route::delete('devices/{id}',    [AccountController::class, 'revokeDevice'])->whereNumber('id');

    Route::get('transactions',       [AccountController::class, 'transactions']);

    Route::get('notifications',              [AccountController::class, 'notifications']);
    Route::post('notifications/{id}/read',   [AccountController::class, 'readNotification'])->whereNumber('id');
    Route::post('notifications/read-all',    [AccountController::class, 'readAllNotifications']);

    Route::post('feedback',          [AccountController::class, 'submitFeedback']);

    Route::get('settings',           [AccountController::class, 'getSettings']);
    Route::post('settings',          [AccountController::class, 'updateSettings']);

    // Firebase push: register this device's FCM token.
    Route::post('push-token',        [AccountController::class, 'registerPushToken']);

    // KYC verification (document upload -> admin approval).
    Route::get('kyc',                [AccountController::class, 'kycStatus']);
    Route::post('kyc',               [AccountController::class, 'submitKyc']);

    // "Get a Callback" from the Help screen -> shows in admin panel.
    Route::post('callback',          [AccountController::class, 'requestCallback']);
});


Route::prefix('business')->middleware('user.api')->group(function () {

    // The "Select Business Profile" list.
    Route::get('/', [BusinessController::class, 'index']);

    // Add New Business wizard: dropdowns, pincode lookup, category search, create.
    Route::get('form-data',            [BusinessController::class, 'formData']);
    Route::get('pincode/{pin}',        [BusinessController::class, 'pincode'])->whereNumber('pin');
    Route::get('subcategories/search', [BusinessController::class, 'searchSubcategories']);
    Route::get('subcategories/search/{term}', [BusinessController::class, 'searchSubcategories']);
    Route::get('sub-categories/{term}', [BusinessController::class, 'searchSubcategories']);
    Route::post('/',                   [BusinessController::class, 'store']);

    // Lead actions — keyed by enquiry id (ownership checked in the controller).
    Route::post('leads/{enquiry}/star',     [BusinessController::class, 'starLead'])->whereNumber('enquiry');
    Route::post('leads/{enquiry}/hide',     [BusinessController::class, 'hideLead'])->whereNumber('enquiry');
    Route::post('leads/{enquiry}/note',     [BusinessController::class, 'noteLead'])->whereNumber('enquiry');
    Route::post('leads/{enquiry}/reminder', [BusinessController::class, 'reminderLead'])->whereNumber('enquiry');
    Route::post('leads/{enquiry}/read',     [BusinessController::class, 'readLead'])->whereNumber('enquiry');

    // Per-business screens.
    Route::get('{id}/dashboard',     [BusinessController::class, 'dashboard'])->whereNumber('id');
    Route::get('{id}/leads',         [BusinessController::class, 'leads'])->whereNumber('id');
    Route::get('{id}/analytics',     [BusinessController::class, 'analytics'])->whereNumber('id');
    Route::get('{id}/response-time', [BusinessController::class, 'responseTime'])->whereNumber('id');
    Route::get('{id}/competitors',   [BusinessController::class, 'competitors'])->whereNumber('id');
    Route::post('{id}/import-leads', [BusinessController::class, 'importLeads'])->whereNumber('id');

    // Edit Business Profile — section by section (name/address wait for admin
    // approval; everything else applies immediately).
    Route::get('{id}/edit',                [BusinessEditController::class, 'show'])->whereNumber('id');
    Route::post('{id}/name',               [BusinessEditController::class, 'updateName'])->whereNumber('id');
    Route::post('{id}/address',            [BusinessEditController::class, 'updateAddress'])->whereNumber('id');
    Route::post('{id}/contact',            [BusinessEditController::class, 'updateContact'])->whereNumber('id');
    Route::post('{id}/serviceable-area',   [BusinessEditController::class, 'updateServiceableArea'])->whereNumber('id');
    Route::post('{id}/map-location',       [BusinessEditController::class, 'updateMapLocation'])->whereNumber('id');
    Route::post('{id}/timings',            [BusinessEditController::class, 'updateTimings'])->whereNumber('id');
    Route::post('{id}/established',        [BusinessEditController::class, 'updateEstablished'])->whereNumber('id');
    Route::post('{id}/categories',         [BusinessEditController::class, 'updateCategories'])->whereNumber('id');
    Route::post('{id}/website',            [BusinessEditController::class, 'updateWebsite'])->whereNumber('id');
    Route::post('{id}/social',             [BusinessEditController::class, 'updateSocial'])->whereNumber('id');
    Route::post('{id}/video',              [BusinessEditController::class, 'updateVideo'])->whereNumber('id');
    Route::post('{id}/photos',             [BusinessEditController::class, 'uploadPhotos'])->whereNumber('id');
    Route::post('{id}/photos/{imageId}/cover', [BusinessEditController::class, 'setCoverPhoto'])->whereNumber('id')->whereNumber('imageId');
    Route::delete('{id}/photos/{imageId}', [BusinessEditController::class, 'deletePhoto'])->whereNumber('id')->whereNumber('imageId');
    Route::post('{id}/catalogue',          [BusinessEditController::class, 'uploadCatalogue'])->whereNumber('id');

    // Business Tools (profile/review link, share, ratings QR data) + Universal
    // Business Listing interest.
    Route::get('{id}/tools',                [BusinessController::class, 'tools'])->whereNumber('id');
    Route::post('{id}/universal-listing',   [BusinessController::class, 'requestUniversalListing'])->whereNumber('id');
});

Route::prefix('whatsapp')
    ->middleware(['admin.api', 'admin.api.can:whatsapp.view'])
    ->group(function () {

        // Reading
        Route::get('/', [WhatsappApiController::class, 'index']);
        Route::get('filters', [WhatsappApiController::class, 'filters']);
        Route::get('listings', [WhatsappApiController::class, 'listings']);
        Route::get('enquiries', [WhatsappApiController::class, 'enquiries']);
        Route::get('enquiries/{id}', [WhatsappApiController::class, 'enquiry'])->whereNumber('id');
        Route::get('replies', [WhatsappApiController::class, 'replies']);
        Route::get('unread-count', [WhatsappApiController::class, 'unreadCount']);
        Route::get('conversations', [WhatsappApiController::class, 'conversations']);
        Route::get('conversations/{phone}', [WhatsappApiController::class, 'thread']);

        Route::post('replies/read-all', [WhatsappApiController::class, 'markAllRepliesRead']);
        Route::post('replies/{id}/read', [WhatsappApiController::class, 'markReplyRead'])->whereNumber('id');

        // Anything that puts a message on the wire needs whatsapp.send too.
        Route::middleware('admin.api.can:whatsapp.send')->group(function () {
            Route::post('send', [WhatsappApiController::class, 'send']);
            Route::post('conversations/{phone}/reply', [WhatsappApiController::class, 'reply']);
        });

        Route::middleware('admin.api.can:whatsapp.delete')->group(function () {
            Route::delete('messages/{id}', [WhatsappApiController::class, 'deleteMessage'])->whereNumber('id');
            Route::delete('conversations/{phone}', [WhatsappApiController::class, 'deleteConversation']);
        });
    });
    