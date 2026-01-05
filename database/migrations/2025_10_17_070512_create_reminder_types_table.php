<?php

// database/migrations/2025_10_17_000000_create_reminder_types_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reminder_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('reminder_types');
    }
};
