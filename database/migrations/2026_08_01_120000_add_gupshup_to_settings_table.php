<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Moves the Gupshup WhatsApp credentials out of .env and into the
     * settings row so they can be managed from the admin panel. The values
     * currently in .env are copied across once here, so nothing breaks the
     * moment this runs — after that the admin form is the source of truth
     * (see AppServiceProvider, which pushes them into services.gupshup.*).
     */
    public function up(): void
    {
        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'gupshup')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('gupshup')->nullable()->after('recaptcha');
            });
        }

        if (!Schema::hasTable('settings')) {
            return;
        }

        $setting = DB::table('settings')->first();

        if (!$setting || !empty($setting->gupshup)) {
            return;
        }

        $seed = array_filter([
            'api_key'         => env('GUPSHUP_API_KEY'),
            'app_name'        => env('GUPSHUP_APP_NAME'),
            'source'          => env('GUPSHUP_SOURCE'),
            'template_id'     => env('GUPSHUP_TEMPLATE_ID'),
            'otp_template_id' => env('GUPSHUP_OTP_TEMPLATE_ID'),
            'webhook_secret'  => env('GUPSHUP_WEBHOOK_SECRET'),
        ], function ($value) {
            return $value !== null && $value !== '';
        });

        if ($seed) {
            DB::table('settings')
                ->where('id', $setting->id)
                ->update(['gupshup' => json_encode($seed)]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'gupshup')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('gupshup');
            });
        }
    }
};
