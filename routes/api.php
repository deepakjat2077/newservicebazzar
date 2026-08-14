<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\WhatsappApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes — external WhatsApp panel
|--------------------------------------------------------------------------
|
| Consumed by the React panel that runs on its own domain. Everything here is
| stateless bearer-token auth (see AuthenticateAdminApi); no session, no CSRF,
| so config/cors.php has to allow the panel's origin.
|
| The Gupshup webhook is NOT registered here even though it once was: it is
| already served from routes/web.php at /gupshup/webhook, and that is the path
| Gupshup is configured with and the one bootstrap/app.php exempts from CSRF.
| Registering it here as well would quietly publish a second live endpoint for
| the same thing.
|
*/

Route::prefix('admin')->group(function () {

    // Same credentials as /admin — same admins table, same username field.
    // Throttled per username+IP inside the controller.
    Route::post('login', [AdminAuthController::class, 'login'])->name('api.admin.login');

    Route::middleware('admin.api')->group(function () {
        Route::get('me', [AdminAuthController::class, 'me'])->name('api.admin.me');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('api.admin.logout');
    });
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
    