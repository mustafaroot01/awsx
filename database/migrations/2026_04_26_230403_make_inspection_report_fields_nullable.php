<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_reports', function (Blueprint $table) {
            $table->string('wall_material')->nullable()->change();
            $table->string('roof_material')->nullable()->change();
            $table->string('lighting_type')->nullable()->change();
            $table->string('fire_extinguishers_info')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_reports', function (Blueprint $table) {
            $table->string('wall_material')->nullable(false)->change();
            $table->string('roof_material')->nullable(false)->change();
            $table->string('lighting_type')->nullable(false)->change();
            $table->string('fire_extinguishers_info')->nullable(false)->change();
        });
    }
};
