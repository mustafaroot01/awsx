<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('last_name')->comment('تاريخ المواليد');
            $table->string('national_id')->nullable()->after('birth_date')->comment('رقم البطاقة الوطنية');
            $table->string('employee_no')->nullable()->change()->comment('الرقم الوظيفي');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['birth_date', 'national_id']);
            $table->string('employee_no')->nullable(false)->change();
        });
    }
};
