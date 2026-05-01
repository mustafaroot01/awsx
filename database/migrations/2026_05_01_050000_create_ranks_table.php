<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم العنوان الوظيفي');
            $table->enum('type', ['admin', 'producer'])->default('admin')->comment('نوع العنوان: إداري أو منتج');
            $table->unsignedTinyInteger('sort_order')->default(0)->comment('ترتيب العرض');
            $table->boolean('is_active')->default(true)->comment('نشط/غير نشط');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
