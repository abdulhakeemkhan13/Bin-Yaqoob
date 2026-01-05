<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds voucher_id to bill_payments table
     */
    public function up(): void
    {
        if (!Schema::hasColumn('bill_payments', 'voucher_id')) {
            Schema::table('bill_payments', function (Blueprint $table) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            if (Schema::hasColumn('bill_payments', 'voucher_id')) {
                $table->dropColumn('voucher_id');
            }
        });
    }
};
