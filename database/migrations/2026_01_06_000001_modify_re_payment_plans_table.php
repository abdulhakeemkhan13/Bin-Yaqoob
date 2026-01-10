<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('re_payment_plans', function (Blueprint $table) {
            // Drop foreign key and column for project_id
            $table->dropForeign(['re_project_id']);
            $table->dropColumn('re_project_id');
            
            // Add new columns
            $table->boolean('is_active')->default(true)->after('extra_charges');
            $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('re_payment_plans', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['is_active', 'created_by']);
            
            $table->foreignId('re_project_id')->constrained('re_projects')->onDelete('cascade');
        });
    }
};
