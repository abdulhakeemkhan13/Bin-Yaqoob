<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Project/Unit selection
            if (!Schema::hasColumn('deals', 're_project_id')) {
                $table->unsignedBigInteger('re_project_id')->nullable()->after('email');
                $table->foreign('re_project_id')->references('id')->on('re_projects')->onDelete('set null');
            }
            if (!Schema::hasColumn('deals', 're_floor_id')) {
                $table->unsignedBigInteger('re_floor_id')->nullable()->after('re_project_id');
                $table->foreign('re_floor_id')->references('id')->on('re_floors')->onDelete('set null');
            }
            if (!Schema::hasColumn('deals', 're_unit_id')) {
                $table->unsignedBigInteger('re_unit_id')->nullable()->after('re_floor_id');
                $table->foreign('re_unit_id')->references('id')->on('re_units')->onDelete('set null');
            }
            // Price details
            if (!Schema::hasColumn('deals', 'offered_price')) {
                $table->decimal('offered_price', 15, 2)->nullable()->after('re_unit_id');
            }
            if (!Schema::hasColumn('deals', 'discount')) {
                $table->decimal('discount', 15, 2)->nullable()->after('offered_price');
            }
            // Expected closing
            if (!Schema::hasColumn('deals', 'expected_closing_date')) {
                $table->date('expected_closing_date')->nullable()->after('discount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (Schema::hasColumn('deals', 're_project_id')) {
                $table->dropForeign(['re_project_id']);
                $table->dropColumn('re_project_id');
            }
            if (Schema::hasColumn('deals', 're_floor_id')) {
                $table->dropForeign(['re_floor_id']);
                $table->dropColumn('re_floor_id');
            }
            if (Schema::hasColumn('deals', 're_unit_id')) {
                $table->dropForeign(['re_unit_id']);
                $table->dropColumn('re_unit_id');
            }
            if (Schema::hasColumn('deals', 'offered_price')) {
                $table->dropColumn('offered_price');
            }
            if (Schema::hasColumn('deals', 'discount')) {
                $table->dropColumn('discount');
            }
            if (Schema::hasColumn('deals', 'expected_closing_date')) {
                $table->dropColumn('expected_closing_date');
            }
        });
    }
};
