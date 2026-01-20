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
        Schema::table('re_projects', function (Blueprint $table) {
            $table->string('approval_authority')->nullable()->after('description'); // LDA, CDA, SBCA, etc
            $table->string('noc_number')->nullable()->after('approval_authority');
            $table->date('approval_date')->nullable()->after('noc_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('re_projects', function (Blueprint $table) {
            $table->dropColumn(['approval_authority', 'noc_number', 'approval_date']);
        });
    }
};
