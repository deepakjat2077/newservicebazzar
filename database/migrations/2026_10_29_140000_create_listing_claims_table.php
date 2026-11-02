<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One row per listing handed to a user because they logged in with the number
// printed on that listing (App\Support\ListingClaim). Keeps the previous
// owner so a wrong move can be undone from Admin > Listing Claims.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('user_id');              // who it went to
            $table->unsignedBigInteger('previous_user_id')->nullable();
            $table->string('phone', 15);
            $table->string('status', 10)->default('claimed');   // claimed | held | undone
            $table->string('reason', 120)->nullable();          // why it was only held
            $table->timestamps();

            $table->index(['listing_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_claims');
    }
};
