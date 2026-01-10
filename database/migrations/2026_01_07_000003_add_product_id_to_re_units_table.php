<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('re_units', function (Blueprint $table) {
            if (!Schema::hasColumn('re_units', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('re_floor_id');
                $table->foreign('product_id')->references('id')->on('product_services')->onDelete('set null');
            }
        });
        Schema::table('product_services', function (Blueprint $table) {
            if (!Schema::hasColumn('product_services', 'project_unit_id')) {
                $table->unsignedBigInteger('project_unit_id')->nullable()->after('id');
                $table->foreign('project_unit_id')->references('id')->on('re_units')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('re_units', function (Blueprint $table) {
            if (Schema::hasColumn('re_units', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
        });

        Schema::table('product_services', function (Blueprint $table) {
            if (Schema::hasColumn('product_services', 'project_unit_id')) {
                $table->dropForeign(['project_unit_id']);
                $table->dropColumn('project_unit_id');
            }
        });
    }
};
