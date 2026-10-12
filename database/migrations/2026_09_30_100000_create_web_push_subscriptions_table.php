<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('endpoint');
            $table->string('p256dh', 512);
            $table->string('auth', 255);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // One endpoint row per user (same device can't register twice)
            $table->unique(['user_id', 'auth']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_push_subscriptions');
    }
};
