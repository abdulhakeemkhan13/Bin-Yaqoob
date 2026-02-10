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
        Schema::table('contracts', function (Blueprint $table) {
            // Fine configuration fields
            $table->decimal('fine_percentage', 8, 2)->nullable()->after('grace_days')->comment('Fine percentage to apply on overdue installments');
            $table->integer('fine_apply_after_due_date')->nullable()->after('fine_percentage')->comment('Number of days after due date before fine applies');
            $table->enum('fine_frequency', ['one_time', 'every_month_after_due'])->nullable()->after('fine_apply_after_due_date')->comment('How often to apply fine');
            
            // Discount configuration fields
            $table->enum('discount_type', ['percentage', 'fixed_amount'])->nullable()->after('fine_frequency')->comment('Type of discount');
            $table->decimal('discount_value', 15, 2)->nullable()->after('discount_type')->comment('Discount percentage or fixed amount value');
            
            // Possession charge fields
            $table->decimal('possession_charge_percentage', 8, 2)->nullable()->after('discount_value')->comment('Possession charge percentage');
            $table->string('possession_charge_type')->nullable()->after('possession_charge_percentage')->comment('Type of possession charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'fine_percentage',
                'fine_apply_after_due_date',
                'fine_frequency',
                'discount_type',
                'discount_value',
                'possession_charge_percentage',
                'possession_charge_type'
            ]);
        });
    }
};
