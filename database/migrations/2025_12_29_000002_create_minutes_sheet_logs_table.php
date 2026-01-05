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
        Schema::create('minutes_sheet_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('minutes_sheet_id');
            $table->unsignedBigInteger('action_by');
            $table->enum('action', ['created', 'approved', 'rejected']);
            $table->text('remarks')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->foreign('minutes_sheet_id')->references('id')->on('minutes_sheets')->onDelete('cascade');
            $table->foreign('action_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minutes_sheet_logs');
    }
};
