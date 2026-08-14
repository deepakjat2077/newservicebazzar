<?php

use App\Models\Location;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        $locations = Location::all();
        foreach ($locations as $location) {
            $slug = Str::slug($location->name);
            $count = 1;
            while (Location::where('slug', $slug)->exists()) {
                $slug = Str::slug($location->name) . '-' . $count;
                $count++;
            }
            $location->slug = $slug;
            $location->save();
        }

        Schema::table('locations', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
