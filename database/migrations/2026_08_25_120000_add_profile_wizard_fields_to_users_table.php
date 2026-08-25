<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The 3-step "FILL PROFILE IN FEW STEPS" wizard needs a handful of personal
 * fields the users table never had — it only ever collected first/last name,
 * one free-text address and a website. Each column is added behind a
 * hasColumn guard because this table predates the migrations folder (no
 * create_users_table migration exists to depend on), so this has to be safe
 * to run against whatever the live schema already looks like.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'title')) {
                $table->string('title', 10)->nullable()->after('username');
            }
            if (!Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'dob')) {
                $table->date('dob')->nullable();
            }
            if (!Schema::hasColumn('users', 'marital_status')) {
                $table->string('marital_status', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('users', 'area')) {
                $table->string('area')->nullable();
            }
            if (!Schema::hasColumn('users', 'pincode')) {
                $table->string('pincode', 10)->nullable();
            }
            if (!Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation')->nullable();
            }
            if (!Schema::hasColumn('users', 'profile_step')) {
                // How far through the wizard the user has gotten, so the
                // sidebar can show a filled tick instead of re-asking a step
                // that was already completed on a previous visit.
                $table->unsignedTinyInteger('profile_step')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['title', 'middle_name', 'dob', 'marital_status', 'city', 'area', 'pincode', 'occupation', 'profile_step'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
