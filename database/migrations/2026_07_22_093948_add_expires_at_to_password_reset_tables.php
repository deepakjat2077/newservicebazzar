<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds an expiry column to both the user and admin password-reset code
     * tables so reset codes can no longer remain valid indefinitely.
     */
    public function up(): void
    {
        if (Schema::hasTable('password_resets') && !Schema::hasColumn('password_resets', 'expires_at')) {
            Schema::table('password_resets', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('token');
            });
        }

        if (Schema::hasTable('admin_password_resets') && !Schema::hasColumn('admin_password_resets', 'expires_at')) {
            Schema::table('admin_password_resets', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('password_resets') && Schema::hasColumn('password_resets', 'expires_at')) {
            Schema::table('password_resets', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }

        if (Schema::hasTable('admin_password_resets') && Schema::hasColumn('admin_password_resets', 'expires_at')) {
            Schema::table('admin_password_resets', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }
    }
};
