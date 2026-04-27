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
            // Detailed Address & Occupation
            $table->string('occupation')->nullable()->after('client_name')->comment('المهنة');
            $table->string('mahalla')->nullable()->after('occupation')->comment('المحلة');
            $table->string('zuqaq')->nullable()->after('mahalla')->comment('الزقاق');
            $table->string('dar')->nullable()->after('zuqaq')->comment('الدار');

            // AML Fields
            $table->string('source_of_funds')->nullable()->comment('مصدر الأموال');
            $table->decimal('monthly_income', 18, 2)->nullable()->comment('الدخل الشهري');
            $table->string('business_type')->nullable()->comment('نوع نشاط الشركة/العمل');
            $table->string('aml_officer_name')->nullable()->comment('اسم مسؤول الإبلاغ');
            $table->timestamp('aml_signed_at')->nullable()->comment('تاريخ توقيع مسؤول الإبلاغ');
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn([
                'occupation', 'mahalla', 'zuqaq', 'dar',
                'source_of_funds', 'monthly_income', 'business_type',
                'aml_officer_name', 'aml_signed_at'
            ]);
        });
    }
};
