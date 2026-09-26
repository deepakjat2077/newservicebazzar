<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The full employee record: where they live, how else to reach them, their
     * ID proofs and the account salary is paid into. Until now a sales user was
     * only a name, a code and a password.
     */
    public function up(): void
    {
        Schema::table('sales_users', function (Blueprint $table) {
            $table->string('alt_phone', 15)->nullable()->after('phone');
            $table->date('joined_on')->nullable()->after('designation');

            $table->string('address', 255)->nullable()->after('city');
            $table->string('area', 120)->nullable()->after('address');
            $table->string('state', 80)->nullable()->after('area');
            $table->string('pincode', 10)->nullable()->after('state');

            $table->string('aadhaar_no', 20)->nullable()->after('pincode');
            $table->string('aadhaar_image', 191)->nullable()->after('aadhaar_no');
            $table->string('pan_no', 15)->nullable()->after('aadhaar_image');
            $table->string('pan_image', 191)->nullable()->after('pan_no');

            $table->string('account_holder', 120)->nullable()->after('pan_image');
            $table->string('bank_name', 120)->nullable()->after('account_holder');
            $table->string('bank_account', 40)->nullable()->after('bank_name');
            $table->string('bank_ifsc', 20)->nullable()->after('bank_account');
            $table->string('bank_branch', 120)->nullable()->after('bank_ifsc');
        });
    }

    public function down(): void
    {
        Schema::table('sales_users', function (Blueprint $table) {
            $table->dropColumn([
                'alt_phone', 'joined_on', 'address', 'area', 'state', 'pincode',
                'aadhaar_no', 'aadhaar_image', 'pan_no', 'pan_image',
                'account_holder', 'bank_name', 'bank_account', 'bank_ifsc', 'bank_branch',
            ]);
        });
    }
};
