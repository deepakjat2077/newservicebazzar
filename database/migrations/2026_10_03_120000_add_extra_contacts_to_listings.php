<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Further people to contact at a business, besides the main contact person:
 * [{title, name, designation, mobile}, ...]. The existing `contact` column is
 * left alone — the admin and owner forms keep their own data in it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->text('extra_contacts')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('extra_contacts');
        });
    }
};
