<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('visitor_sessions')) {
            return;
        }

        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id')->index();
            $table->uuid('session_uuid')->unique();
            $table->string('laravel_session_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('referrer_url')->nullable();
            $table->text('landing_page')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('device_type')->nullable()->index();
            $table->string('browser')->nullable()->index();
            $table->string('os')->nullable();
            $table->string('screen_resolution')->nullable();
            $table->string('country')->nullable()->index();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('page_view_count')->default(0);
            $table->timestamps();

            $table->index(['started_at', 'page_view_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_sessions');
    }
};
