<?php

// database/migrations/2025_10_16_100002_alter_check_lists_add_bill_refs.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        });

        // Make cheque_number nullable if the column exists & isn't already nullable.
        // (Requires doctrine/dbal for ->change(); otherwise use raw SQL)
        if (Schema::hasColumn('check_lists', 'cheque_number')) {
            try {
                Schema::table('check_lists', function (Blueprint $table) {
                    $table->string('cheque_number', 100)->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Fallback raw SQL (MySQL)
                DB::statement('ALTER TABLE `check_lists` MODIFY `cheque_number` VARCHAR(100) NULL;');
            }
        }

        // Add composite index only if it does NOT exist
        if (!$this->indexExists('check_lists', 'check_lists_bill_id_bill_payment_id_index')) {
            Schema::table('check_lists', function (Blueprint $table) {
                $table->index(['bill_id', 'bill_payment_id'], 'check_lists_bill_id_bill_payment_id_index');
            });
        }

        if (!$this->indexExists('check_lists', 'check_lists_type_index')) {
            Schema::table('check_lists', function (Blueprint $table) {
                $table->index('type', 'check_lists_type_index');
            });
        }
    }

    public function down(): void
    {
        // optional clean-up
        if ($this->indexExists('check_lists', 'check_lists_bill_id_bill_payment_id_index')) {
            Schema::table('check_lists', function (Blueprint $table) {
                $table->dropIndex('check_lists_bill_id_bill_payment_id_index');
            });
        }
        if ($this->indexExists('check_lists', 'check_lists_type_index')) {
            Schema::table('check_lists', function (Blueprint $table) {
                $table->dropIndex('check_lists_type_index');
            });
        }
        Schema::table('check_lists', function (Blueprint $table) {
            if (Schema::hasColumn('check_lists', 'bill_payment_id')) $table->dropColumn('bill_payment_id');
            if (Schema::hasColumn('check_lists', 'bill_id')) $table->dropColumn('bill_id');
            if (Schema::hasColumn('check_lists', 'type')) $table->dropColumn('type');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $dbName = DB::getDatabaseName();
        $count = DB::table('information_schema.statistics')
            ->where('table_schema', $dbName)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->count();

        return $count > 0;
    }
};
