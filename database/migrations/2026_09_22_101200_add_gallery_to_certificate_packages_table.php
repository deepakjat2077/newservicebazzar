<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_packages', function (Blueprint $table) {
            // Extra preview images shown as slides after the main image.
            $table->json('gallery')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_packages', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });
    }
};
