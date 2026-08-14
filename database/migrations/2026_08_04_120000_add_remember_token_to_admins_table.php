<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admins had no remember_token, so "stay logged in" could never work for the
 * panel — Laravel's session guard needs this column to re-authenticate from
 * the recaller cookie. Without it every admin was silently logged out the
 * moment their session expired (120 minutes idle), which is what turned a
 * slow listing form into a rejected save.
 *
 * The model already lists remember_token in $hidden; only the column was
 * missing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('admins', 'remember_token')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('admins', 'remember_token')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
