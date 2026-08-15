<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['visitors', 'visitor_sessions', 'page_views', 'analytics_events'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'is_bot')) {
                    $table->boolean('is_bot')->default(false)->index();
                }

                if (!Schema::hasColumn($tableName, 'user_agent')) {
                    $table->text('user_agent')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['visitors', 'visitor_sessions', 'page_views', 'analytics_events'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'user_agent')) {
                    $table->dropColumn('user_agent');
                }

                if (Schema::hasColumn($tableName, 'is_bot')) {
                    $table->dropColumn('is_bot');
                }
            });
        }
    }
};
