<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'source')) {
                $table->string('source')->nullable()->index();
            }
            if (!Schema::hasColumn('listings', 'sales_user_id')) {
                $table->unsignedBigInteger('sales_user_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['source', 'sales_user_id']);
        });
    }
};
