<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelistings', function (Blueprint $table) {
            if (!Schema::hasColumn('freelistings', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('freelistings', 'landline')) {
                $table->string('landline', 20)->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('freelistings', 'website')) {
                $table->string('website')->nullable()->after('email');
            }
            if (!Schema::hasColumn('freelistings', 'source')) {
                $table->string('source')->nullable()->after('status');
            }
            if (!Schema::hasColumn('freelistings', 'sales_user_id')) {
                $table->unsignedBigInteger('sales_user_id')->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('freelistings', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'landline', 'website', 'source', 'sales_user_id']);
        });
    }
};
