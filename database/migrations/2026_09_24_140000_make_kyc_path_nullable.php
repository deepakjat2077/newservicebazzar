<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A KYC row may now hold only the document number — the rep can take down
     * a GSTIN or PAN before the scan is in hand, and the number is what the
     * customer's invoice needs.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE listing_kyc_documents MODIFY path VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE listing_kyc_documents SET path = '' WHERE path IS NULL");
        DB::statement('ALTER TABLE listing_kyc_documents MODIFY path VARCHAR(255) NOT NULL');
    }
};
