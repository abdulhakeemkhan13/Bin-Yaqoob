<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('re_unit_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('re_unit_id')->constrained('re_units')->onDelete('cascade');
            $table->foreignId('re_payment_plan_id')->constrained('re_payment_plans')->onDelete('cascade');
            $table->decimal('price_override', 15, 2)->nullable(); // Override unit price for this plan
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['re_unit_id', 're_payment_plan_id'], 'unit_plan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('re_unit_payment_plans');
    }
};
