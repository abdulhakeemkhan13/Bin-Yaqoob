<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns to journal_entries table
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('re_project_id')->nullable()->after('employee_id');
            $table->unsignedBigInteger('tower_id')->nullable()->after('re_project_id');
            $table->unsignedBigInteger('floor_id')->nullable()->after('tower_id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('floor_id');
        });

        // Add columns to journal_items table
        Schema::table('journal_items', function (Blueprint $table) {
            $table->unsignedBigInteger('re_project_id')->nullable()->after('prod_tax_id');
            $table->unsignedBigInteger('tower_id')->nullable()->after('re_project_id');
            $table->unsignedBigInteger('floor_id')->nullable()->after('tower_id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('floor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['re_project_id', 'tower_id', 'floor_id', 'unit_id']);
        });

        Schema::table('journal_items', function (Blueprint $table) {
            $table->dropColumn(['re_project_id', 'tower_id', 'floor_id', 'unit_id']);
        });
    }
};
