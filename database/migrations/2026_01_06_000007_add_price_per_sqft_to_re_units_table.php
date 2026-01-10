<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('re_units', function (Blueprint $table) {
            if (!Schema::hasColumn('re_units', 'price_per_sqft')) {
                $table->decimal('price_per_sqft', 12, 2)->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('re_units', function (Blueprint $table) {
            if (Schema::hasColumn('re_units', 'price_per_sqft')) {
                $table->dropColumn('price_per_sqft');
            }
        });
    }
};
