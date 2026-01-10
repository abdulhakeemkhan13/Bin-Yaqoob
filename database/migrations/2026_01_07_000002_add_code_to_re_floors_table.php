<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First add the column as nullable without unique
        Schema::table('re_floors', function (Blueprint $table) {
            if (!Schema::hasColumn('re_floors', 'code')) {
                $table->string('code', 50)->nullable()->after('re_project_id');
            }
        });

        // Generate codes for existing floors
        $floors = DB::table('re_floors')
            ->join('re_projects', 're_floors.re_project_id', '=', 're_projects.id')
            ->select('re_floors.id', 're_projects.code as project_code', 're_floors.floor_number')
            ->get();

        foreach ($floors as $floor) {
            DB::table('re_floors')
                ->where('id', $floor->id)
                ->update(['code' => $floor->project_code . '_' . $floor->floor_number]);
        }

        // Now add the unique constraint
        Schema::table('re_floors', function (Blueprint $table) {
            $table->string('code', 50)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('re_floors', function (Blueprint $table) {
            if (Schema::hasColumn('re_floors', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};

