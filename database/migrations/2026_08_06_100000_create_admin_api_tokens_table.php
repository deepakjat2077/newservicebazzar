<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bearer tokens for the external WhatsApp panel.
 *
 * The admin panel authenticates with the session-based `admin` guard, which a
 * React app on a different domain cannot use — cookies are not shared across
 * origins. This table backs a token guard instead: the panel logs in once,
 * gets a token, and sends it as `Authorization: Bearer …` from then on.
 *
 * Deliberately not Sanctum: adding a composer package would mean running
 * composer (or shipping vendor/) on the production host for what amounts to
 * one table and one middleware. Swapping to Sanctum later only touches
 * AuthenticateAdminApi and this model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_api_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_id')
                ->constrained('admins')
                ->cascadeOnDelete();

            // Free-text label so an admin can tell "WhatsApp panel — office
            // laptop" from a token issued to someone else's browser.
            $table->string('name', 191)->default('whatsapp-panel');

            // SHA-256 of the plaintext half. The plaintext is returned exactly
            // once, at login, and is never recoverable from this row — a dump
            // of this table does not hand anyone a working token.
            $table->string('token', 64)->unique();

            // Mirrors the admin's whatsapp.* permissions at issue time, so a
            // stolen token can't do more than the panel it was minted for.
            $table->json('abilities')->nullable();

            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();

            $table->timestamps();

            $table->index(['admin_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_api_tokens');
    }
};
