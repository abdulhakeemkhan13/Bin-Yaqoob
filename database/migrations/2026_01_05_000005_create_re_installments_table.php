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
        Schema::create('re_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('re_payment_plan_id');
            $table->integer('installment_no');
            $table->date('due_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['Pending', 'Paid', 'Overdue'])->default('Pending');
            $table->foreign('re_payment_plan_id')->references('id')->on('re_payment_plans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('re_installments');
    }
};
