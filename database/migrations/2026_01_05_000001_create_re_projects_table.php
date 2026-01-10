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
        Schema::create('re_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->string('city');
            $table->string('area')->nullable();
            $table->text('address')->nullable();
            $table->integer('total_floors')->default(0);
            $table->integer('total_units')->default(0);
            $table->enum('type', ['Residential', 'Commercial', 'Mixed'])->default('Residential');
            $table->enum('status', ['Planning', 'Active', 'Completed'])->default('Planning');
            $table->date('start_date')->nullable();
            $table->date('expected_completion')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('re_projects');
    }
};
