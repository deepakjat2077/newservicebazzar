<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_views')) {
            return;
        }

        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_page_view_id')->unique();
            $table->uuid('visitor_id')->index();
            $table->uuid('session_uuid')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('listing_id')->nullable()->index();
            $table->text('url');
            $table->string('page_name')->nullable()->index();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->boolean('is_exit')->default(false);
            $table->timestamps();

            $table->index(['session_uuid', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
