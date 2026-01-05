<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds voucher_id to invoices and invoice_payments tables for voucher tracking
     */
    public function up(): void
    {
        // Add voucher_id to invoices table
        if (!Schema::hasColumn('invoices', 'voucher_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('id');
            });
        }

        // Add owned_by to invoices table if not exists
        if (!Schema::hasColumn('invoices', 'owned_by')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->integer('owned_by')->nullable()->after('created_by');
            });
        }

        // Add voucher_id to invoice_payments table
        if (!Schema::hasColumn('invoice_payments', 'voucher_id')) {
            Schema::table('invoice_payments', function (Blueprint $table) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'voucher_id')) {
                $table->dropColumn('voucher_id');
            }
            if (Schema::hasColumn('invoices', 'owned_by')) {
                $table->dropColumn('owned_by');
            }
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_payments', 'voucher_id')) {
                $table->dropColumn('voucher_id');
            }
        });
    }
};
