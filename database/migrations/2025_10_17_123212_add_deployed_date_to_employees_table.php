<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_deployed_date_to_employees_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'deployed_date')) {
                $table->date('deployed_date')->nullable()->after('deployed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('deployed_date');
        });
    }
};
