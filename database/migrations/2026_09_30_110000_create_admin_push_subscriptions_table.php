<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->text('endpoint');
            $table->string('p256dh', 512);
            $table->string('auth', 255);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique(['admin_id', 'auth']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_push_subscriptions');
    }
};
