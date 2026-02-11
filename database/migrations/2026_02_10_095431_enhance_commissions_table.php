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
        Schema::table('commissions', function (Blueprint $table) {
            $table->enum('commission_for', ['dealer', 'company_management', 'employee'])->default('employee')->after('employee_id');
            $table->integer('dealer_id')->nullable()->after('commission_for');
            $table->unsignedBigInteger('contract_id')->nullable()->after('dealer_id');
            $table->unsignedBigInteger('deal_id')->nullable()->after('contract_id');
            $table->decimal('commission_percentage', 15, 2)->default(0)->after('amount');
            $table->decimal('total_commission_amount', 15, 2)->default(0)->after('commission_percentage');
            $table->enum('release_condition_type', ['percentage_received', 'on_installment'])->default('on_installment')->after('total_commission_amount');
            $table->decimal('release_percentage', 5, 2)->nullable()->after('release_condition_type'); // e.g., 80.00
            $table->enum('status', ['pending', 'partially_released', 'fully_released'])->default('pending')->after('release_percentage');
            $table->decimal('amount_released', 15, 2)->default(0)->after('status');
            $table->text('notes')->nullable()->after('amount_released');
            
            // Allow employee_id to be nullable since we now have dealers and management
            $table->integer('employee_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn([
                'commission_for',
                'dealer_id',
                'contract_id',
                'deal_id',
                'commission_percentage',
                'total_commission_amount',
                'release_condition_type',
                'release_percentage',
                'status',
                'amount_released',
                'notes'
            ]);
            $table->integer('employee_id')->nullable(false)->change();
        });
    }
};
