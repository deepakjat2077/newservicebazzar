<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KYC verification + Firebase (FCM) push support for the mobile app.
 *
 * KYC is a real, reviewed verification: the user uploads an ID document, it
 * lands as `pending`, and an admin approves or rejects it — the same
 * pending-approval pattern the site already uses for business name/address
 * changes. (Automated ID-provider checks would need a third-party KYC API key;
 * this is the admin-reviewed flow, which needs no external secret.)
 *
 * Push: each login token (device) carries its own FCM registration token, so a
 * notification can be delivered to every device the user is signed in on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // aadhaar | pan | gst | driving_license | passport | other
            $table->string('document_type', 40);
            $table->string('document_number', 100)->nullable();
            $table->string('front_image', 255)->nullable();
            $table->string('back_image', 255)->nullable();

            // 0 pending, 1 approved, 2 rejected — same scheme as payments.
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->string('admin_note', 500)->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // Per-device FCM registration token, for push delivery.
        if (Schema::hasColumn('user_api_tokens', 'id') && !Schema::hasColumn('user_api_tokens', 'fcm_token')) {
            Schema::table('user_api_tokens', function (Blueprint $table) {
                $table->string('fcm_token', 255)->nullable()->after('provider');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_submissions');

        if (Schema::hasColumn('user_api_tokens', 'fcm_token')) {
            Schema::table('user_api_tokens', function (Blueprint $table) {
                $table->dropColumn('fcm_token');
            });
        }
    }
};
