<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Callback requests from the mobile app's Help screen ("Get a Callback").
 *
 * The user asks to be called back; the request lands in the admin panel where
 * the team sees it, calls the number, and marks it handled — the same simple
 * pending/handled review shape KYC uses.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('callback_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 191)->nullable();
            $table->string('phone', 20);
            // Which help topic it's about (Leads issues, My payments, etc.).
            $table->string('topic', 100)->nullable();
            $table->text('message')->nullable();

            // 0 pending, 1 handled/called.
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->string('admin_note', 500)->nullable();
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_requests');
    }
};
