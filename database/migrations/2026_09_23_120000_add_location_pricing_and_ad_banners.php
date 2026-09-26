<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A price row is now identified by its tier label ("Top 1", "1 Year",
        // "2 Days") so the same table serves position pricing and plain
        // city/area plan pricing.
        Schema::table('package_position_prices', function (Blueprint $table) {
            $table->string('tier', 60)->default('')->after('area');
            $table->unsignedSmallInteger('days')->nullable()->after('price');
        });

        DB::statement("UPDATE package_position_prices SET tier = CONCAT('Top ', position) WHERE tier = ''");

        Schema::table('package_position_prices', function (Blueprint $table) {
            $table->dropUnique('pos_price_unique');
            $table->unique(['certificate_package_id', 'city', 'area', 'tier'], 'pos_price_tier_unique');
            $table->unsignedTinyInteger('position')->nullable()->default(null)->change();
        });

        Schema::table('certificate_packages', function (Blueprint $table) {
            // After payment, ask the buyer for their banner/ad details and
            // build the creative automatically.
            $table->boolean('needs_ad_details')->default(false)->after('validity_days');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('sales_city', 100)->nullable()->after('sales_days');
            $table->string('sales_area', 150)->nullable()->after('sales_city');
        });

        Schema::table('listing_sales_packages', function (Blueprint $table) {
            $table->unsignedSmallInteger('days')->nullable()->after('position');
        });

        Schema::create('listing_ad_banners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index();
            $table->unsignedBigInteger('certificate_package_id')->index();
            $table->unsignedBigInteger('listing_sales_package_id')->nullable()->index();
            $table->string('city', 100)->nullable();
            $table->string('area', 150)->nullable();
            $table->string('title', 120)->nullable();
            $table->string('subtitle', 160)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('photo')->nullable();   // buyer's own photo, optional
            $table->string('image')->nullable();   // generated banner
            // 0 = details pending, 1 = live, 2 = paused by admin
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_ad_banners');
        Schema::table('listing_sales_packages', fn (Blueprint $t) => $t->dropColumn('days'));
        Schema::table('payments', fn (Blueprint $t) => $t->dropColumn(['sales_city', 'sales_area']));
        Schema::table('certificate_packages', fn (Blueprint $t) => $t->dropColumn('needs_ad_details'));
        Schema::table('package_position_prices', function (Blueprint $table) {
            $table->dropUnique('pos_price_tier_unique');
            $table->dropColumn(['tier', 'days']);
        });
    }
};
