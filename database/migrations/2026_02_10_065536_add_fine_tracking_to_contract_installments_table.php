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
        Schema::table('contract_installments', function (Blueprint $table) {
            // Fine tracking fields
            $table->decimal('fine_amount', 15, 2)->default(0)->after('description')->comment('Total fine applied to this installment');
            $table->date('fine_applied_date')->nullable()->after('fine_amount')->comment('When fine was first applied');
            $table->date('last_fine_calculation_date')->nullable()->after('fine_applied_date')->comment('Last time monthly fine was calculated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_installments', function (Blueprint $table) {
            $table->dropColumn([
                'fine_amount',
                'fine_applied_date',
                'last_fine_calculation_date'
            ]);
        });
    }
};
