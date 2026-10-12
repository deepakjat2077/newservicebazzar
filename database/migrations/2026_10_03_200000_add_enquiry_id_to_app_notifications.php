<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ties an enquiry notification to its enquiry, so deleting the enquiry
 * removes the notification instead of leaving the vendor a lead that is gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('enquiry_id')->nullable()->after('link')->index();
        });
    }

    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropIndex(['enquiry_id']);
            $table->dropColumn('enquiry_id');
        });
    }
};
