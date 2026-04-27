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
        Schema::table('policies', function (Blueprint $table) {
            $table->enum('status', ['deposit', 'acceptance', 'active', 'cancelled', 'expired'])
                ->default('active')
                ->after('category')
                ->comment('حالة الوثيقة');
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
