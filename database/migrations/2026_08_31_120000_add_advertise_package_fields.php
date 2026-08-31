<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Advertise / plans feature.
 *
 * - packages: admin-editable marketing fields so a package can render as a
 *   JustDial-style plan card (a "Features Included" checklist, an optional
 *   struck-through old price, a ribbon tag, and a "popular" highlight).
 * - payments: an optional listing_id so a package purchase can be tied to the
 *   specific business it was bought for, not just the buyer's account.
 * - listings: package_id + package_exp so a paid package activates on the
 *   listing (the level the owner actually advertises at), mirroring the
 *   user-level package_id/package_exp that already exists on users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'features')) {
                $table->text('features')->nullable()->after('validity');
            }
            if (!Schema::hasColumn('packages', 'old_price')) {
                $table->decimal('old_price', 12, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('packages', 'tag')) {
                $table->string('tag')->nullable()->after('features');
            }
            if (!Schema::hasColumn('packages', 'is_popular')) {
                $table->boolean('is_popular')->default(0)->after('tag');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'listing_id')) {
                $table->unsignedBigInteger('listing_id')->nullable()->after('package_id');
            }
        });

        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('listings', 'package_exp')) {
                $table->timestamp('package_exp')->nullable()->after('package_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            foreach (['features', 'old_price', 'tag', 'is_popular'] as $col) {
                if (Schema::hasColumn('packages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'listing_id')) {
                $table->dropColumn('listing_id');
            }
        });

        Schema::table('listings', function (Blueprint $table) {
            foreach (['package_id', 'package_exp'] as $col) {
                if (Schema::hasColumn('listings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
