<?php

use App\Models\Listing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        $listings = Listing::all();
        foreach ($listings as $listing) {
            $slug = Str::slug($listing->name);
            $count = 1;
            while (Listing::where('slug', $slug)->exists()) {
                $slug = Str::slug($listing->name) . '-' . $count;
                $count++;
            }
            $listing->slug = $slug;
            $listing->save();
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
