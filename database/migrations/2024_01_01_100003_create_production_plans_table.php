<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->comment('السنة المالية');
            $table->string('title')->comment('عنوان الخطة');
            $table->decimal('total_amount', 18, 2)->comment('إجمالي مبلغ الخطة');
            $table->boolean('is_locked')->default(false)->comment('هل الخطة مقفلة');
            $table->timestamps();
        });

        Schema::create('production_plan_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('production_plans')->cascadeOnDelete();
            $table->enum('category', ['life', 'group_health', 'general_property'])
                ->comment('فئة التأمين: حياة / صحي جماعي / ممتلكات عامة');
            $table->decimal('target_amount', 18, 2)->comment('المبلغ المستهدف');
            $table->timestamps();
        });

        Schema::create('branch_production_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('production_plans')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->enum('category', ['life', 'group_health', 'general_property']);
            $table->decimal('target_amount', 18, 2)->comment('المستهدف للفرع');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_production_targets');
        Schema::dropIfExists('production_plan_categories');
        Schema::dropIfExists('production_plans');
    }
};
