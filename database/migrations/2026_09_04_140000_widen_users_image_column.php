<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * users.image held a varchar(191). Google / Facebook avatar URLs are ~800
 * characters, so social sign-up failed with "Data too long for column 'image'"
 * and the account was never created. Widen it to comfortably hold any provider
 * avatar URL. Only a column widening — no data is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('image', 2048)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('image', 191)->nullable()->change();
        });
    }
};
