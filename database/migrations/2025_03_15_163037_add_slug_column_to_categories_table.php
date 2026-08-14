<?php

use App\Models\Category;
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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        $categories = Category::all();
        foreach ($categories as $categorie) {
            $slug = Str::slug($categorie->name);
            $count = 1;
            while (Category::where('slug', $slug)->exists()) {
                $slug = Str::slug($categorie->name) . '-' . $count;
                $count++;
            }
            $categorie->slug = $slug;
            $categorie->save();
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
