<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('check_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('check_lists', 'minutes_sheet_id')) {
                $table->unsignedBigInteger('minutes_sheet_id')->nullable()->after('bill_payment_id');
                $table->foreign('minutes_sheet_id')->references('id')->on('minutes_sheets')->onDelete('cascade');
            }
            if (!Schema::hasColumn('check_lists', 'payee_name')) {
                $table->string('payee_name')->nullable()->after('minutes_sheet_id');
            }
            if (!Schema::hasColumn('check_lists', 'account_number')) {
                $table->string('account_number')->nullable()->after('account_title');
            }
            if (!Schema::hasColumn('check_lists', 'bank_coa')) {
                $table->unsignedBigInteger('bank_coa')->nullable()->after('account_id');
            }
            if (!Schema::hasColumn('check_lists', 'other_coa')) {
                $table->unsignedBigInteger('other_coa')->nullable()->after('bank_coa');
            }
            if (!Schema::hasColumn('check_lists', 'journal_entry_id')) {
                $table->unsignedBigInteger('journal_entry_id')->nullable()->after('other_coa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('check_lists', function (Blueprint $table) {
            $table->dropForeign(['minutes_sheet_id']);
            $table->dropColumn(['minutes_sheet_id', 'payee_name']);
        });
    }
};
