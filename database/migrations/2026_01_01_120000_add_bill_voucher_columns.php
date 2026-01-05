<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds missing columns needed for Bill voucher creation
     */
    public function up(): void
    {
        // Add owned_by to journal_entries if missing
        if (!Schema::hasColumn('journal_entries', 'owned_by')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->unsignedBigInteger('owned_by')->nullable()->after('created_by');
            });
        }

        // Add product_ids and prod_tax_id to journal_items if missing
        if (!Schema::hasColumn('journal_items', 'product_ids')) {
            Schema::table('journal_items', function (Blueprint $table) {
                $table->unsignedBigInteger('product_ids')->nullable()->after('description');
            });
        }
        if (!Schema::hasColumn('journal_items', 'prod_tax_id')) {
            Schema::table('journal_items', function (Blueprint $table) {
                $table->unsignedBigInteger('prod_tax_id')->nullable()->after('product_ids');
            });
        }

        // Add product tracking columns to transaction_lines if missing
        if (!Schema::hasColumn('transaction_lines', 'product_id')) {
            Schema::table('transaction_lines', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable()->after('created_by');
            });
        }
        if (!Schema::hasColumn('transaction_lines', 'product_type')) {
            Schema::table('transaction_lines', function (Blueprint $table) {
                $table->string('product_type', 100)->nullable()->after('product_id');
            });
        }
        if (!Schema::hasColumn('transaction_lines', 'product_item_id')) {
            Schema::table('transaction_lines', function (Blueprint $table) {
                $table->unsignedBigInteger('product_item_id')->nullable()->after('product_type');
            });
        }

        // Add voucher_id and owned_by to bills if missing
        if (!Schema::hasColumn('bills', 'voucher_id')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('bills', 'owned_by')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->unsignedBigInteger('owned_by')->nullable()->after('created_by');
            });
        }
        if (!Schema::hasColumn('bills', 'ref_number')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->string('ref_number')->nullable()->after('order_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'owned_by')) {
                $table->dropColumn('owned_by');
            }
        });

        Schema::table('journal_items', function (Blueprint $table) {
            if (Schema::hasColumn('journal_items', 'product_ids')) {
                $table->dropColumn('product_ids');
            }
            if (Schema::hasColumn('journal_items', 'prod_tax_id')) {
                $table->dropColumn('prod_tax_id');
            }
        });

        Schema::table('transaction_lines', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_lines', 'product_id')) {
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('transaction_lines', 'product_type')) {
                $table->dropColumn('product_type');
            }
            if (Schema::hasColumn('transaction_lines', 'product_item_id')) {
                $table->dropColumn('product_item_id');
            }
        });

        Schema::table('bills', function (Blueprint $table) {
            if (Schema::hasColumn('bills', 'voucher_id')) {
                $table->dropColumn('voucher_id');
            }
            if (Schema::hasColumn('bills', 'owned_by')) {
                $table->dropColumn('owned_by');
            }
            if (Schema::hasColumn('bills', 'ref_number')) {
                $table->dropColumn('ref_number');
            }
        });
    }
};
