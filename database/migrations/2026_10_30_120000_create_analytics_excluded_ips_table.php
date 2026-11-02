<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Addresses whose traffic is not analytics.
 *
 * The office wifi, a phone on it, a staff laptop — every one of them was
 * landing in the visitor counts as a stranger. The exclusion itself already
 * existed, but only as ANALYTICS_EXCLUDED_IPS in .env, which meant editing a
 * file on both servers every time a connection changed its address. This is
 * the same list, kept where it can actually be maintained.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_excluded_ips', function (Blueprint $table) {
            $table->id();
            // An address, or a range: 49.36.x.x, or 2409:4080:d3e:2fb2::/64
            // for a home connection, where every device holds its own IPv6
            // address under one shared prefix.
            $table->string('value', 64)->unique();
            $table->string('label', 120)->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_excluded_ips');
    }
};
