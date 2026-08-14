<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190)->unique();
            $table->string('phone', 30)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('token', 64)->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('guest_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_user_id')->constrained('guest_users')->cascadeOnDelete();
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->tinyInteger('attempts')->default(0);
            $table->timestamps();
            $table->index(['guest_user_id', 'code']);
        });

        Schema::create('guest_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('guest_users')->cascadeOnDelete();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->unique(['listing_id', 'guest_user_id']);
        });

        Schema::create('guest_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('guest_conversations')->cascadeOnDelete();
            $table->enum('sender_type', ['guest', 'owner']);
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_messages');
        Schema::dropIfExists('guest_conversations');
        Schema::dropIfExists('guest_otps');
        Schema::dropIfExists('guest_users');
    }
};
