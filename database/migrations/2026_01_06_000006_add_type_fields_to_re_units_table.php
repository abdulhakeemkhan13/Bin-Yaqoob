<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('re_units', function (Blueprint $table) {
            // Common fields
            if (!Schema::hasColumn('re_units', 'description')) {
                $table->text('description')->nullable()->after('status');
            }
            
            // Flat specific fields
            if (!Schema::hasColumn('re_units', 'bedrooms')) {
                $table->integer('bedrooms')->nullable()->after('description');
            }
            if (!Schema::hasColumn('re_units', 'bathrooms')) {
                $table->integer('bathrooms')->nullable()->after('bedrooms');
            }
            if (!Schema::hasColumn('re_units', 'balconies')) {
                $table->integer('balconies')->nullable()->after('bathrooms');
            }
            if (!Schema::hasColumn('re_units', 'has_parking')) {
                $table->boolean('has_parking')->default(false)->after('balconies');
            }
            
            // Shop/Office fields
            if (!Schema::hasColumn('re_units', 'floor_position')) {
                $table->string('floor_position')->nullable()->after('has_parking'); // Front/Back/Corner
            }
            if (!Schema::hasColumn('re_units', 'has_mezzanine')) {
                $table->boolean('has_mezzanine')->default(false)->after('floor_position');
            }
            if (!Schema::hasColumn('re_units', 'has_washroom')) {
                $table->boolean('has_washroom')->default(false)->after('has_mezzanine');
            }
            
            // Penthouse fields
            if (!Schema::hasColumn('re_units', 'terrace_area')) {
                $table->decimal('terrace_area', 10, 2)->nullable()->after('has_washroom');
            }
            if (!Schema::hasColumn('re_units', 'is_duplex')) {
                $table->boolean('is_duplex')->default(false)->after('terrace_area');
            }
        });
    }

    public function down(): void
    {
        Schema::table('re_units', function (Blueprint $table) {
            $columns = [
                'description', 'bedrooms', 'bathrooms', 'balconies', 'has_parking',
                'floor_position', 'has_mezzanine', 'has_washroom', 'terrace_area', 'is_duplex'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('re_units', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
