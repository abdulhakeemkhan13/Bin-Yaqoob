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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_no')->unique()->nullable()->after('id');
            $table->unsignedBigInteger('re_project_id')->nullable()->after('project_id'); // Using project_id already exists but keeping re_ for consistency if needed
            $table->unsignedBigInteger('tower_id')->nullable()->after('re_project_id');
            $table->unsignedBigInteger('floor_id')->nullable()->after('tower_id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('floor_id');
            $table->decimal('sale_price', 15, 2)->default(0)->after('unit_id');
            $table->decimal('price_per_sqft', 15, 2)->default(0)->after('sale_price');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('price_per_sqft');
            $table->decimal('other_charges', 15, 2)->default(0)->after('discount_amount');
            $table->decimal('net_sale_price', 15, 2)->default(0)->after('other_charges');
            $table->date('booking_date')->nullable()->after('net_sale_price');
            $table->date('agreement_date')->nullable()->after('booking_date');
            $table->date('possession_due_date')->nullable()->after('agreement_date');
            $table->string('payment_plan_type')->nullable()->after('possession_due_date'); // Installment, FullPayment, Custom
            $table->decimal('penalty_percent_per_month', 5, 2)->default(0)->after('payment_plan_type');
            $table->integer('grace_days')->default(0)->after('penalty_percent_per_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'contract_no', 're_project_id', 'tower_id', 'floor_id', 'unit_id', 'sale_price',
                'price_per_sqft', 'discount_amount', 'other_charges', 'net_sale_price', 'booking_date',
                'agreement_date', 'possession_due_date', 'payment_plan_type', 'penalty_percent_per_month', 'grace_days'
            ]);
        });
    }
};
