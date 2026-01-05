<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_other_deduction_to_pay_slips_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            // store arbitrary rows for this month only (title+amount)
            $table->text('other_deduction')->nullable()->after('other_payment');
        });
    }
    public function down(): void
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            $table->dropColumn('other_deduction');
        });
    }
};

