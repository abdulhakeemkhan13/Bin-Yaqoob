<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->nullable()->after('customer_id');
            $table->unsignedBigInteger('installment_id')->nullable()->after('contract_id');
            $table->unsignedBigInteger('re_project_id')->nullable()->after('installment_id');
            $table->unsignedBigInteger('tower_id')->nullable()->after('re_project_id');
            $table->unsignedBigInteger('floor_id')->nullable()->after('tower_id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('floor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['contract_id', 'installment_id', 're_project_id', 'tower_id', 'floor_id', 'unit_id']);
        });
    }
};
