<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('re_project_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('re_project_id')->constrained('re_projects')->onDelete('cascade');
            $table->foreignId('re_payment_plan_id')->constrained('re_payment_plans')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['re_project_id', 're_payment_plan_id'], 'project_plan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('re_project_payment_plans');
    }
};
