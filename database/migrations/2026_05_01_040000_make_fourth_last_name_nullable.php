<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('fourth_name')->nullable()->change()->comment('اسم الجد الثاني');
            $table->string('last_name')->nullable()->change()->comment('اللقب');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('fourth_name')->nullable(false)->change()->comment('اسم الجد الثاني');
            $table->string('last_name')->nullable(false)->change()->comment('اللقب');
        });
    }
};
