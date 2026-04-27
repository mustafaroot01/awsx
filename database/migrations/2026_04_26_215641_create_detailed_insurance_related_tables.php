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
        // 1. Funds Detail Schedule (Building, Goods, Machinery, Furniture)
        Schema::create('policy_funds_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->enum('category', ['building', 'goods', 'machinery', 'furniture']);
            $table->decimal('value', 18, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Inspection Reports (Fire & Theft / Buildings)
        Schema::create('inspection_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->string('wall_material')->comment('مواد الجدران');
            $table->string('roof_material')->comment('مواد السقوف');
            $table->string('lighting_type')->comment('وسيلة الإنارة');
            $table->string('fire_extinguishers_info')->comment('أدوات الإطفاء');
            $table->text('inspector_recommendation')->nullable()->comment('توصية الكاشف');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('inspector_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 3. Life Policy Beneficiaries
        Schema::create('policy_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('share_survival', 5, 2)->comment('حصة البقاء حياً %');
            $table->decimal('share_death', 5, 2)->comment('حصة الوفاة %');
            $table->string('relationship');
            $table->timestamps();
        });

        // 4. Health Statements (Life Insurance)
        Schema::create('health_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->unique()->constrained('policies')->cascadeOnDelete();
            // 10 items health statement (storing as JSON for flexibility, or individual columns)
            $table->json('health_items')->comment('10 فقرات صحية');
            $table->json('family_history')->comment('التاريخ الطبي للعائلة');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_statements');
        Schema::dropIfExists('policy_beneficiaries');
        Schema::dropIfExists('inspection_reports');
        Schema::dropIfExists('policy_funds_schedule');
    }
};
