<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Every sign-in to the admin panel (and failed tries): who, when, where from, on what. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('username', 191)->nullable();
            $table->string('event', 20)->index(); // login, auto_login, failed, logout
            $table->string('ip', 64)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('country', 120)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('accuracy_m')->nullable();
            $table->string('location_source', 10)->nullable(); // gps or ip
            $table->string('address', 500)->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('os', 60)->nullable();
            $table->string('device', 20)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_logs');
    }
};
