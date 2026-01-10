<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('re_installments', function (Blueprint $table) {
            // Drop old foreign key to payment_plans if exists
            if (Schema::hasColumn('re_installments', 're_payment_plan_id')) {
                $table->dropForeign(['re_payment_plan_id']);
                $table->dropColumn('re_payment_plan_id');
            }
            
            // Add booking reference if not exists
            if (!Schema::hasColumn('re_installments', 're_booking_id')) {
                $table->foreignId('re_booking_id')->after('id')->constrained('re_bookings')->onDelete('cascade');
            }
            
            // Add payment tracking columns if not exist
            if (!Schema::hasColumn('re_installments', 'amount_paid')) {
                $table->decimal('amount_paid', 15, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('re_installments', 'paid_date')) {
                $table->date('paid_date')->nullable()->after('amount_paid');
            }
            if (!Schema::hasColumn('re_installments', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('paid_date');
            }
            if (!Schema::hasColumn('re_installments', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('re_installments', function (Blueprint $table) {
            if (Schema::hasColumn('re_installments', 're_booking_id')) {
                $table->dropForeign(['re_booking_id']);
                $table->dropColumn('re_booking_id');
            }
            if (Schema::hasColumn('re_installments', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }
            if (Schema::hasColumn('re_installments', 'paid_date')) {
                $table->dropColumn('paid_date');
            }
            if (Schema::hasColumn('re_installments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('re_installments', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
            
            // Add back the old column
            if (!Schema::hasColumn('re_installments', 're_payment_plan_id')) {
                $table->foreignId('re_payment_plan_id')->constrained('re_payment_plans')->onDelete('cascade');
            }
        });
    }
};
