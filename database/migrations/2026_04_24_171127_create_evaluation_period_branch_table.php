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
        Schema::create('evaluation_period_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_period_id')->constrained('evaluation_periods')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->timestamps();
        });

        // We can also remove the old branch_id from evaluation_periods if we want,
        // but let's keep it simple and just use this new table.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_period_branch');
    }
};
