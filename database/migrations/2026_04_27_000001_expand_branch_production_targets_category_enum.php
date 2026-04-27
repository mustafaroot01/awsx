<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Expand the category ENUM to include all 8 sub-categories
        DB::statement("ALTER TABLE branch_production_targets
            MODIFY COLUMN category ENUM(
                'life',
                'group_health',
                'general_property',
                'vehicle',
                'fire_theft',
                'transport_marine',
                'engineering',
                'personal_accident',
                'cash'
            ) NOT NULL");
    }

    public function down(): void
    {
        // Revert to original 3 values (will fail if data contains new values)
        DB::statement("ALTER TABLE branch_production_targets
            MODIFY COLUMN category ENUM(
                'life',
                'group_health',
                'general_property'
            ) NOT NULL");
    }
};
