<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Professional/YMYL listings (doctors, lawyers, CAs) can now carry a public
 * registration credential — the strongest trust signal Google looks for in
 * these categories. Two nullable text fields: a label ("Bar Council ID",
 * "MCI Reg. No.") and the number itself. Empty for existing rows; shown on
 * the listing only when filled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('registration_label', 100)->nullable()->after('established_year');
            $table->string('registration_number', 100)->nullable()->after('registration_label');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['registration_label', 'registration_number']);
        });
    }
};
