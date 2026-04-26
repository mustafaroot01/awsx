<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->comment('السنة');
            $table->unsignedTinyInteger('period_no')->comment('رقم الفترة 1-6 (كل شهرين)');
            $table->date('start_date')->comment('تاريخ البدء');
            $table->date('end_date')->comment('تاريخ الانتهاء');
            $table->enum('status', ['open', 'locked'])->default('open')->comment('حالة الفترة');
            $table->timestamps();
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('evaluation_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            // Evaluation scores (0-100 each criterion)
            $table->unsignedTinyInteger('score_attendance')->default(0)->comment('الحضور والانضباط');
            $table->unsignedTinyInteger('score_performance')->default(0)->comment('الأداء الوظيفي');
            $table->unsignedTinyInteger('score_behavior')->default(0)->comment('السلوك المهني');
            $table->unsignedTinyInteger('score_production')->default(0)->comment('الإنتاجية');
            $table->unsignedTinyInteger('score_teamwork')->default(0)->comment('العمل الجماعي');
            $table->unsignedSmallInteger('total_score')->default(0)->comment('المجموع الكلي');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();

            $table->unique(['period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('evaluation_periods');
    }
};
