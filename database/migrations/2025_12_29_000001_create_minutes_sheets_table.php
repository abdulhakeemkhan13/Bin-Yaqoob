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
        Schema::create('minutes_sheets', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('reference_no')->unique();
            $table->string('subject');
            $table->text('description');
            $table->unsignedBigInteger('bank_account_id');
            $table->unsignedBigInteger('created_by');
            $table->string('designation'); // Creator's designation at creation time
            $table->string('current_stage'); // Current approval stage designation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minutes_sheets');
    }
};
