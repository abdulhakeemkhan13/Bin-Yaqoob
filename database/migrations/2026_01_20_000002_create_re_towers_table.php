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
        Schema::create('re_towers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('re_project_id');
            $table->foreign('re_project_id')->references('id')->on('re_projects')->onDelete('cascade');
            $table->string('tower_code'); // A, B, C or Block-1 etc
            $table->string('tower_name')->nullable();
            $table->integer('floors_count')->default(0);
            $table->integer('basement_floors')->default(0);
            $table->enum('construction_type', ['RCC', 'Steel', 'Composite', 'Other'])->default('RCC');
            $table->string('foundation_type')->nullable();
            $table->integer('elevator_count')->default(0);
            $table->enum('parking_type', ['Basement', 'Podium', 'Mechanical', 'Open', 'None'])->default('None');
            $table->enum('status', ['Planning', 'Construction', 'Completed'])->default('Planning');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('re_towers');
    }
};
