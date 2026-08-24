<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Google/Facebook "continue with" login credentials, managed from the
     * admin panel (Setting > Social Login) the same way Gupshup's WhatsApp
     * credentials are — see AppServiceProvider, which pushes these into
     * services.google.* / services.facebook.* for Socialite to read.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'social_login')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('social_login')->nullable()->after('gupshup');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'social_login')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('social_login');
            });
        }
    }
};
