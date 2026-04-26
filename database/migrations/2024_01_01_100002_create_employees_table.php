<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no')->unique()->comment('الرقم الوظيفي');
            $table->string('first_name')->comment('الاسم الأول');
            $table->string('second_name')->comment('اسم الأب');
            $table->string('third_name')->comment('اسم الجد');
            $table->string('fourth_name')->comment('اسم الجد الثاني');
            $table->string('last_name')->comment('اللقب');
            $table->string('degree')->comment('الدرجة الوظيفية');
            $table->string('rank')->comment('المرتبة الوظيفية');
            $table->string('education')->comment('الشهادة العلمية');
            $table->enum('gender', ['male', 'female'])->comment('الجنس');
            $table->enum('job_type', ['permanent', 'contract', 'daily_wage'])->comment('نوع الوظيفة');
            $table->string('production_no')->nullable()->comment('الرقم الإنتاجي');
            $table->date('hire_date')->comment('تاريخ التعيين');
            $table->string('avatar')->nullable()->comment('صورة الموظف');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->timestamps();
        });

        // Add foreign keys to branches after employees table is created
        // SQLite does not support ALTER TABLE ADD CONSTRAINT, skip for sqlite driver
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            Schema::table('branches', function (Blueprint $table) {
                $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
                $table->foreign('deputy_id')->references('id')->on('employees')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropForeign(['manager_id']);
                $table->dropForeign(['deputy_id']);
            });
        }

        Schema::dropIfExists('employees');
    }
};
