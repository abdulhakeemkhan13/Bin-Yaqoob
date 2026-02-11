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
        Schema::table('journal_items', function (Blueprint $table) {
            $table->string('type')->nullable()->after('credit');
            $table->string('name')->nullable()->after('type');
            $table->unsignedBigInteger('customer_id')->nullable()->after('name');
            $table->unsignedBigInteger('vendor_id')->nullable()->after('customer_id');
            $table->unsignedBigInteger('employee_id')->nullable()->after('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_items', function (Blueprint $table) {
            $table->dropColumn(['type', 'name', 'customer_id', 'vendor_id', 'employee_id']);
        });
    }
};
