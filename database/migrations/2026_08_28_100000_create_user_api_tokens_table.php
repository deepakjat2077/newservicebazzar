<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bearer tokens for the mobile app (Service Bazzar Android/iOS).
 *
 * The website authenticates users with the session-based `web` guard, which a
 * native app cannot use — it holds no cookies. This table backs a token guard
 * instead: the app logs in once (WhatsApp OTP, Google, or Facebook), gets a
 * token, and sends it as `Authorization: Bearer …` from then on.
 *
 * Same shape and reasoning as admin_api_tokens — see that migration and
 * App\Models\UserApiToken. Deliberately not Sanctum: one table + one
 * middleware, no new composer package on the production host.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_api_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Free-text label so a user (and support) can tell one device's
            // token from another — "iPhone 13", "Redmi Note".
            $table->string('name', 191)->default('mobile-app');

            // SHA-256 of the plaintext half. The plaintext is returned exactly
            // once, at login, and is never recoverable from this row.
            $table->string('token', 64)->unique();

            // How the token was obtained: whatsapp | google | facebook. Handy
            // for support and for revoking a whole login method at once.
            $table->string('provider', 20)->default('whatsapp');

            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();

            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_api_tokens');
    }
};
