<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('re_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('re_unit_id')->constrained('re_units')->onDelete('cascade');
            $table->foreignId('re_payment_plan_id')->constrained('re_payment_plans')->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_cnic')->nullable();
            $table->string('customer_email')->nullable();
            $table->date('booking_date');
            $table->decimal('total_price', 15, 2); // Unit price at booking time
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('extra_charges', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2); // After adjustments
            $table->enum('status', ['Active', 'Cancelled', 'Completed', 'Transferred'])->default('Active');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('re_bookings');
    }
};
