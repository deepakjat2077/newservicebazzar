<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // A payment is for EITHER a legacy package_id OR a sales package.
            $table->unsignedBigInteger('sales_package_id')->nullable()->after('package_id');
            $table->string('sales_plan', 150)->nullable()->after('sales_package_id');
            $table->unsignedSmallInteger('sales_days')->nullable()->after('sales_plan');
        });

        Schema::table('certificate_packages', function (Blueprint $table) {
            // Used when the package is position-priced (plans carry their own).
            $table->unsignedSmallInteger('validity_days')->default(365)->after('positions');
        });

        Schema::create('listing_sales_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index();
            $table->unsignedBigInteger('certificate_package_id')->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->string('plan', 150);
            $table->decimal('price', 12, 2);
            $table->string('city', 100)->nullable();
            $table->string('area', 150)->nullable();
            $table->unsignedTinyInteger('position')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_sales_packages');
        Schema::table('payments', fn (Blueprint $t) => $t->dropColumn(['sales_package_id', 'sales_plan', 'sales_days']));
        Schema::table('certificate_packages', fn (Blueprint $t) => $t->dropColumn('validity_days'));
    }
};
