<?php

// database/migrations/2025_10_16_000001_add_type_and_cheque_to_invoice_payments.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->string('type')->nullable()->after('payment_method'); // cash|cheque|direct_deposit
            $table->string('cheque_number')->nullable()->after('type');
            $table->string('add_receipt')->nullable()->change(); // if not already nullable
            $table->string('type_flow')->nullable()->after('cheque_number'); // your "Partial" if needed
            $table->index(['type', 'cheque_number']);
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropIndex(['type', 'cheque_number']);
            $table->dropColumn(['type','cheque_number','type_flow']);
        });
    }
};
