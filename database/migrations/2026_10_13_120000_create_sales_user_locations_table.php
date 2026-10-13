<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Where a sales rep was when they logged in and while using the SB Sales panel
// (browser GPS, with the rep's permission), and which of those points each
// disposition was saved at.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_user_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_user_id');
            $table->string('event', 20);            // login | ping
            $table->string('status', 20);           // ok | denied | unavailable | timeout
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->unsignedInteger('accuracy')->nullable();   // metres, as the browser reports it
            $table->string('place')->nullable();               // reverse-geocoded, filled after the response
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['sales_user_id', 'created_at']);
        });

        Schema::table('listing_dispositions', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('listing_dispositions', function (Blueprint $table) {
            $table->dropColumn('location_id');
        });
        Schema::dropIfExists('sales_user_locations');
    }
};
