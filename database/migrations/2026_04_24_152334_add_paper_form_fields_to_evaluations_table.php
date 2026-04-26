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
        Schema::table('evaluations', function (Blueprint $table) {
            // Qualitative fields (Excellent, etc.)
            $table->string('efficiency_experience')->nullable();
            $table->string('speed_of_achievement')->nullable();
            $table->string('sense_of_responsibility')->nullable();
            $table->string('behavior_with_others')->nullable();
            $table->string('attendance_commitment')->nullable();
            $table->string('appreciation_penalties')->nullable();

            // Incentive points (Calculated)
            $table->integer('points_competency')->default(0);
            $table->integer('points_grade')->default(0);
            $table->integer('points_education')->default(0);
            $table->integer('points_service')->default(0);
            $table->integer('points_total')->default(0);
            
            // New days field for incentive calculation context
            $table->integer('actual_working_days')->default(60); // Default for 2 months
            $table->integer('net_working_days')->default(60);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'efficiency_experience', 'speed_of_achievement', 'sense_of_responsibility',
                'behavior_with_others', 'attendance_commitment', 'appreciation_penalties',
                'points_competency', 'points_grade', 'points_education', 'points_service',
                'points_total', 'actual_working_days', 'net_working_days'
            ]);
        });
    }
};
