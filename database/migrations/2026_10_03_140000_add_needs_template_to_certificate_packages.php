<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Packages that include a website ask the rep to pick one of the website designs
 * before the package goes into the cart. Flip this on for any other package to
 * give it the same step.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_packages', function (Blueprint $table) {
            $table->boolean('needs_template')->default(false);
        });

        DB::table('certificate_packages')->where('name', 'Website + GMB Combo')->update(['needs_template' => true]);
    }

    public function down(): void
    {
        Schema::table('certificate_packages', function (Blueprint $table) {
            $table->dropColumn('needs_template');
        });
    }
};
