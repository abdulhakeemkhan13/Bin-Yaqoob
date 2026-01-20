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
        Schema::table('re_payment_plans', function (Blueprint $table) {
            $table->decimal('booking_charges', 15, 2)->nullable()->after('extra_charges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('re_payment_plans', function (Blueprint $table) {
            $table->dropColumn('booking_charges');
        });
    }
};
