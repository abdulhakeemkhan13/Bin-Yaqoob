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
        Schema::create('frozen_installment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->decimal('down_payment_amount', 15, 2)->default(0);
            $table->date('down_payment_due_date')->nullable();
            $table->integer('installment_count')->default(0);
            $table->string('installment_frequency')->default('Monthly'); // Monthly, Quarterly
            $table->decimal('installment_amount', 15, 2)->default(0);
            $table->decimal('possession_amount', 15, 2)->default(0);
            $table->decimal('balloon_amount', 15, 2)->nullable();
            $table->decimal('total_payable', 15, 2)->default(0);
            $table->date('plan_start_date')->nullable();
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frozen_installment_plans');
    }
};
