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
        Schema::create('re_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('re_floor_id');
            $table->string('unit_number');
            $table->enum('unit_type', ['Flat', 'Shop', 'Office', 'Penthouse'])->default('Flat');
            $table->decimal('covered_area', 10, 2)->nullable()->comment('in sq ft');
            $table->decimal('price', 15, 2)->default(0);
            $table->string('facing')->nullable();
            $table->enum('status', ['Available', 'Booked', 'Sold', 'Reserved'])->default('Available');
            $table->foreign('re_floor_id')->references('id')->on('re_floors')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('re_units');
    }
};
