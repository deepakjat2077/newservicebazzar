<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mobile_verified')) {
                $table->boolean('mobile_verified')->default(0)->after('email_verified');
            }
        });

        // Accounts already created by the WhatsApp OTP login: their address is
        // a mobile-derived placeholder, so the OTP is the only proof of
        // identity they have or will ever have.
        DB::table('users')
            ->where('email', 'like', '%@whatsapp.servicebazzar.com')
            ->update(['mobile_verified' => 1]);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mobile_verified')) {
                $table->dropColumn('mobile_verified');
            }
        });
    }
};
