<?php

// database/migrations/2025_10_16_000002_create_check_lists_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('check_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('invoice_payment_id');
            $table->unsignedBigInteger('account_id');
            $table->string('cheque_number', 100);
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('status')->default('Pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('owned_by')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('invoice_payment_id')->references('id')->on('invoice_payments')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('bank_accounts')->restrictOnDelete();

            $table->index(['cheque_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_lists');
    }
};

