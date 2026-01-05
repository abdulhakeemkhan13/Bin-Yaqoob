<?php

// database/migrations/2025_10_16_100002_alter_check_lists_add_bill_refs.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('check_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('check_lists', 'bill_id')) {
                $table->unsignedBigInteger('bill_id')->nullable()->after('invoice_payment_id');
            }
            if (!Schema::hasColumn('check_lists', 'bill_payment_id')) {
                $table->unsignedBigInteger('bill_payment_id')->nullable()->after('bill_id');
            }
            if (!Schema::hasColumn('check_lists', 'type')) {
                $table->string('type')->nullable()->after('account_id'); // cash|cheque|direct_deposit
            }
            // make sure cheque_number is nullable
            $table->string('cheque_number', 100)->nullable()->change();

            // indexes (optional but useful)
            $table->index(['bill_id', 'bill_payment_id']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::table('check_lists', function (Blueprint $table) {
            // You can keep bill columns if you want; otherwise:
            // $table->dropIndex([...]); $table->dropColumn(['bill_id','bill_payment_id','type']);
        });
    }
};

