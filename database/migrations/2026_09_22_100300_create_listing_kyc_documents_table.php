<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index();
            $table->string('doc_type', 40)->index();   // pan, address, aadhaar, gst, msme, driving_licence, voter_id, office_photos
            $table->string('doc_number')->nullable();
            $table->string('path');
            $table->string('status', 20)->default('uploaded'); // uploaded | in_review | approved | rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_kyc_documents');
    }
};
