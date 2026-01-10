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
            $table->unsignedBigInteger('payment_plan_id')->nullable()->after('project_id');
            $table->integer('num_installments')->nullable()->after('payment_plan_id');
            $table->decimal('down_payment_percent', 5, 2)->nullable()->after('num_installments');
            $table->decimal('down_payment_amount', 15, 2)->nullable()->after('down_payment_percent');
            $table->unsignedBigInteger('deal_id')->nullable()->after('down_payment_amount');
            
            $table->foreign('payment_plan_id')->references('id')->on('re_payment_plans')->onDelete('set null');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['payment_plan_id']);
            $table->dropForeign(['deal_id']);
            $table->dropColumn(['payment_plan_id', 'num_installments', 'down_payment_percent', 'down_payment_amount', 'deal_id']);
        });
    }
};
