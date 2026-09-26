<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Browsers a rep has already passed the OTP on.
     *
     * The OTP step used to be skipped whenever sales_users.last_verified_at was
     * under 24 hours old. That is a fact about the account, not about the
     * device — so once a rep verified anywhere, anyone holding the password
     * could sign in from any browser for the next day without an OTP. Trust
     * belongs to the browser that proved it, and nowhere else.
     */
    public function up(): void
    {
        Schema::create('sales_trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_user_id')->index();
            // Only the hash is stored; the raw token lives in the rep's cookie.
            $table->string('token_hash', 64)->unique();
            $table->string('user_agent', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_trusted_devices');
    }
};
