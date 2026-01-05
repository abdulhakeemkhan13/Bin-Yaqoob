<?php

// database/migrations/2025_10_16_000003_alter_check_lists_add_type_and_nullable_cheque.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('check_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('check_lists', 'type')) {
                $table->string('type')->after('account_id')->index(); // cash|cheque|direct_deposit
            }
            $table->string('cheque_number', 100)->nullable()->change();
        });
    }
    public function down(): void
    {
        Schema::table('check_lists', function (Blueprint $table) {
            // optional: drop column if needed
            // $table->dropColumn('type');
        });
    }
};
