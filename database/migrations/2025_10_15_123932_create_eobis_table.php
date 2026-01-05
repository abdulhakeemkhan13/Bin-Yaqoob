<?php

// database/migrations/2025_10_15_000002_create_eobis_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eobis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id')->index();
            $table->string('title', 191);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('eobis');
    }
};

