<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backing tables for the mobile app's account-section features that the
 * website has no equivalent of: in-app notifications, app feedback
 * (report a bug / suggest a feature), and per-user app settings.
 *
 * Kept deliberately small and self-contained — these are the app's own
 * engagement surfaces, not part of the listing/enquiry domain.
 */
return new class extends Migration
{
    public function up(): void
    {
        // In-app notifications — one row per recipient (broadcast is done by
        // fanning out rows), so `is_read` is simply per-row.
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 191);
            $table->text('body')->nullable();
            $table->string('image', 255)->nullable();
            // Where tapping it should take the user in the app (a deep link or
            // a listing/enquiry id encoded by the app).
            $table->string('link', 255)->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });

        // App feedback — "Report a Bug" / "Suggest a Feature".
        Schema::create('app_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20)->default('bug'); // bug | feature
            $table->string('subject', 191)->nullable();
            $table->text('message');
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });

        // Per-user app settings (notification toggles, display name override,
        // etc.) as one JSON blob — the shape is app-driven and low-stakes, so
        // it doesn't warrant a column per toggle.
        if (!Schema::hasColumn('users', 'app_settings')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('app_settings')->nullable()->after('social');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_feedbacks');
        Schema::dropIfExists('app_notifications');

        if (Schema::hasColumn('users', 'app_settings')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('app_settings');
            });
        }
    }
};
