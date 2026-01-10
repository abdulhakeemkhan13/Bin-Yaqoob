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
        Schema::create('re_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('re_project_id');
            $table->string('plan_name');
            $table->integer('duration_months')->default(12);
            $table->decimal('down_payment_percentage', 5, 2)->nullable();
            $table->decimal('down_payment_amount', 15, 2)->nullable();
            $table->integer('num_installments')->default(12);
            $table->enum('frequency', ['Monthly', 'Quarterly'])->default('Monthly');
            $table->decimal('possession_charges', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('extra_charges', 15, 2)->nullable();
            $table->foreign('re_project_id')->references('id')->on('re_projects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('re_payment_plans');
    }
};
