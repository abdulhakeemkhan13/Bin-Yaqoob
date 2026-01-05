<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds vendor_id, customer_id, employee_id to journal_entries
     */
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('category');
            }
            if (!Schema::hasColumn('journal_entries', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('vendor_id');
            }
            if (!Schema::hasColumn('journal_entries', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('customer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'vendor_id')) {
                $table->dropColumn('vendor_id');
            }
            if (Schema::hasColumn('journal_entries', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('journal_entries', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
        });
    }
};
