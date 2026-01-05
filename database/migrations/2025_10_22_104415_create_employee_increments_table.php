<?php

// database/migrations/2025_10_16_000000_create_employee_increments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_increments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->index();
            // Per your note: we are giving previous_salary in migration and we will fetch it from employees
            $table->decimal('previous_salary', 12, 2)->nullable(); // keep if you want history; remove if not needed
            $table->decimal('increment_amount', 12, 2);
            $table->date('increment_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_increments');
    }
};
