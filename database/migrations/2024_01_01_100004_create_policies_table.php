<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_no')->unique()->comment('رقم الوثيقة');
            $table->enum('category', [
                'vehicle',           // وثيقة التأمين على السيارات
                'fire_theft',        // تأمين ضد أخطار الحريق والسرقة
                'group_health',      // وثيقة التأمين الصحي الجماعي
                'transport_marine',  // النقل / البحري
                'engineering',       // وثيقة التأمين الهندسي
                'life',              // وثيقة التأمين على الحياة
                'personal_accident', // تأمين الحوادث الشخصية
                'cash',              // وثيقة تأمين النقد
            ])->comment('فئة التأمين');
            $table->string('client_name')->comment('اسم العميل');
            $table->decimal('amount', 18, 2)->comment('مبلغ الوثيقة');
            $table->date('issue_date')->comment('تاريخ الإصدار');
            $table->date('expiry_date')->comment('تاريخ الانتهاء');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
        });

        Schema::create('life_policy_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->unique()->constrained('policies')->cascadeOnDelete();
            $table->enum('payment_cycle', ['annual', 'quarterly', 'monthly', 'semi_annual'])
                ->comment('دورية السداد');
            $table->decimal('accident_fee', 18, 2)->default(0)->comment('رسوم الحوادث الثابتة');
            $table->unsignedSmallInteger('duration_years')->default(2)->comment('مدة التأمين بالسنوات (الحد الأدنى 2)');
            // KYC Data
            $table->string('id_number')->nullable()->comment('رقم الهوية');
            $table->date('birth_date')->nullable()->comment('تاريخ الميلاد');
            $table->string('phone')->nullable()->comment('رقم الهاتف');
            $table->string('address')->nullable()->comment('العنوان');
            $table->string('beneficiary_name')->nullable()->comment('اسم المستفيد');
            $table->string('beneficiary_relation')->nullable()->comment('صلة القرابة');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('life_policy_details');
        Schema::dropIfExists('policies');
    }
};
