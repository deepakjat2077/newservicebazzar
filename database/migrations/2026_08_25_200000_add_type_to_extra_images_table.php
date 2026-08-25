<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Rate Card / Catalogue" images are stored the same way gallery photos
 * already are (same table, same upload path) — `type` is just how a row
 * says which section it belongs to, so the gallery page and the catalogue
 * page each only ever see their own images.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extra_images', function (Blueprint $table) {
            if (!Schema::hasColumn('extra_images', 'type')) {
                $table->string('type', 20)->default('gallery')->after('listing_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('extra_images', function (Blueprint $table) {
            if (Schema::hasColumn('extra_images', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
