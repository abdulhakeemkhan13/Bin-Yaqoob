<?php

// database/migrations/2025_10_16_100001_add_type_and_cheque_to_bill_payments.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_payments', 'type')) {
                $table->string('type')->nullable()->after('payment_method'); // cash|cheque|direct_deposit
            }
            if (!Schema::hasColumn('bill_payments', 'cheque_number')) {
                $table->string('cheque_number', 100)->nullable()->after('type');
            }
            $table->index(['type', 'cheque_number']);
        });
    }

    public function down(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->dropIndex(['type', 'cheque_number']);
            $table->dropColumn(['type', 'cheque_number']);
        });
    }
};

