<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('customer_type')->nullable()->after('re_unit_id');
            $table->string('full_name')->nullable()->after('customer_type');
            $table->string('father_or_company_name')->nullable()->after('full_name');
            $table->string('cnic_or_ntn')->nullable()->after('father_or_company_name');
            $table->string('mobile_primary')->nullable()->after('cnic_or_ntn');
            $table->string('mobile_secondary')->nullable()->after('mobile_primary');
            $table->text('current_address')->nullable()->after('email');
            $table->text('permanent_address')->nullable()->after('current_address');
            $table->string('nationality')->nullable()->after('permanent_address');
            
            $table->string('nominee_name')->nullable()->after('nationality');
            $table->string('nominee_relation')->nullable()->after('nominee_name');
            $table->string('nominee_cnic')->nullable()->after('nominee_relation');
            $table->string('nominee_contact')->nullable()->after('nominee_cnic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'customer_type',
                'full_name',
                'father_or_company_name',
                'cnic_or_ntn',
                'mobile_primary',
                'mobile_secondary',
                'current_address',
                'permanent_address',
                'nationality',
                'nominee_name',
                'nominee_relation',
                'nominee_cnic',
                'nominee_contact'
            ]);
        });
    }
};
