<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->string('monthly_income')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->decimal('monthly_income', 18, 2)->nullable()->change();
        });
    }
};
