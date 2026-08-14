<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreelistingsTable extends Migration
{
    public function up()
    {
        Schema::create('freelistings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->string('business_name');
            $table->string('pincode');
            $table->string('plot_no')->nullable();
            $table->string('building_name')->nullable();
            $table->string('street_name')->nullable();
            $table->string('landmark')->nullable();
            $table->string('area')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();

            $table->tinyInteger('status')->default(0); 
            // 0 = Pending
            // 1 = Approved
            // 2 = Rejected

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('freelistings');
    }
}