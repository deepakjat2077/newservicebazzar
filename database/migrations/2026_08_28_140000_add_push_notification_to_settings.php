<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Firebase (FCM) push credentials, managed from the admin panel instead of
 * .env — same pattern as the gupshup / social_login columns. Holds the service
 * account JSON the admin pastes in (Setting > Push Notification), which
 * App\Services\FcmService uses to send push via the FCM HTTP v1 API.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'push_notification')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->json('push_notification')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'push_notification')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('push_notification');
            });
        }
    }
};
