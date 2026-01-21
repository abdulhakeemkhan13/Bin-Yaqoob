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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('client_type')->nullable()->after('customer_id');
            $table->string('full_name')->nullable()->after('client_type');
            $table->string('father_or_spouse_name')->nullable()->after('full_name');
            $table->date('date_of_birth')->nullable()->after('father_or_spouse_name');
            $table->string('nationality')->nullable()->after('date_of_birth');
            $table->string('cnic_number')->nullable()->after('nationality');
            $table->date('cnic_issue_date')->nullable()->after('cnic_number');
            $table->date('cnic_expiry_date')->nullable()->after('cnic_issue_date');
            $table->string('company_name')->nullable()->after('cnic_expiry_date');
            $table->string('company_reg_no')->nullable()->after('company_name');
            $table->string('ntn_number')->nullable()->after('company_reg_no');
            $table->string('sales_tax_no')->nullable()->after('ntn_number');
            $table->string('mobile_primary')->nullable()->after('sales_tax_no');
            $table->string('mobile_secondary')->nullable()->after('mobile_primary');
            $table->text('current_address')->nullable()->after('mobile_secondary');
            $table->text('permanent_address')->nullable()->after('current_address');
            $table->string('city')->nullable()->after('permanent_address'); // Already there? user requested it.
            $table->string('country')->nullable()->after('city');
            $table->string('source_of_funds')->nullable()->after('country');
            $table->string('employer_or_business')->nullable()->after('source_of_funds');
            $table->string('filer_status')->nullable()->after('employer_or_business');
            $table->boolean('is_pep')->default(false)->after('filer_status');
            $table->string('client_status')->default('Active')->after('is_pep');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'client_type', 'full_name', 'father_or_spouse_name', 'date_of_birth', 'nationality',
                'cnic_number', 'cnic_issue_date', 'cnic_expiry_date', 'company_name', 'company_reg_no',
                'ntn_number', 'sales_tax_no', 'mobile_primary', 'mobile_secondary', 'current_address',
                'permanent_address', 'city', 'country', 'source_of_funds', 'employer_or_business',
                'filer_status', 'is_pep', 'client_status'
            ]);
        });
    }
};
