<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_kyc_documents', function (Blueprint $table) {
            $table->json('ai_check')->nullable()->after('status');
            $table->timestamp('ai_checked_at')->nullable()->after('ai_check');
        });
    }

    public function down(): void
    {
        Schema::table('listing_kyc_documents', function (Blueprint $table) {
            $table->dropColumn(['ai_check', 'ai_checked_at']);
        });
    }
};
