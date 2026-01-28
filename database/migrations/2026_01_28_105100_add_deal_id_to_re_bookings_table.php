<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('re_bookings', function (Blueprint $table) {
            // Add deal_id column
            $table->unsignedBigInteger('deal_id')->nullable()->after('id');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('set null');
            
            // Make re_payment_plan_id nullable for draft bookings
            $table->unsignedBigInteger('re_payment_plan_id')->nullable()->change();
        });

        // Update status enum to include 'Draft'
        DB::statement("ALTER TABLE re_bookings MODIFY COLUMN status ENUM('Draft', 'Active', 'Cancelled', 'Completed', 'Transferred') DEFAULT 'Draft'");
    }

    public function down(): void
    {
        Schema::table('re_bookings', function (Blueprint $table) {
            $table->dropForeign(['deal_id']);
            $table->dropColumn('deal_id');
        });

        DB::statement("ALTER TABLE re_bookings MODIFY COLUMN status ENUM('Active', 'Cancelled', 'Completed', 'Transferred') DEFAULT 'Active'");
    }
};
