<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE policy_funds_schedule
            MODIFY COLUMN category ENUM(
                'building',
                'goods',
                'machinery',
                'furniture',
                'others'
            ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE policy_funds_schedule
            MODIFY COLUMN category ENUM(
                'building',
                'goods',
                'machinery',
                'furniture'
            ) NOT NULL");
    }
};
