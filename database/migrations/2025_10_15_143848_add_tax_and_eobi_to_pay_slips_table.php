<?php

// database/migrations/2025_10_15_000001_add_tax_and_eobi_to_pay_slips_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            // Use json if your MySQL >= 5.7, otherwise change to ->text()
            if (!Schema::hasColumn('pay_slips', 'tax'))  {
                $table->json('tax')->nullable()->after('overtime');
            }
            if (!Schema::hasColumn('pay_slips', 'eobi')) {
                $table->json('eobi')->nullable()->after('tax');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            if (Schema::hasColumn('pay_slips', 'eobi')) $table->dropColumn('eobi');
            if (Schema::hasColumn('pay_slips', 'tax'))  $table->dropColumn('tax');
        });
    }
};
