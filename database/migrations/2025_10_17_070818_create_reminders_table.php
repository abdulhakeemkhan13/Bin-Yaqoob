<?php

// database/migrations/2025_10_17_000001_create_reminders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reminder_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // owner/creator
            $table->string('title');
            $table->text('description')->nullable();

            $table->date('due_date');            // actual event/deadline date
            $table->unsignedInteger('before_days')->default(0); // how many days before to start reminding
            $table->date('remind_from_date');    // computed: due_date - before_days

            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            // bookkeeping
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('reminders');
    }
};
