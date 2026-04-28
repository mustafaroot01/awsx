<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AddProductionPlanPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure read.ProductionPlan permission exists (safe - no truncate)
        Permission::firstOrCreate(
            ['slug' => 'read.ProductionPlan'],
            ['name' => 'عرض الخطط الإنتاجية', 'category' => 'الخطط الإنتاجية']
        );

        // Also ensure other ProductionPlan permissions exist
        $permissions = [
            ['slug' => 'create.ProductionPlan', 'name' => 'إضافة خطة إنتاجية',  'category' => 'الخطط الإنتاجية'],
            ['slug' => 'update.ProductionPlan', 'name' => 'تعديل خطة إنتاجية',  'category' => 'الخطط الإنتاجية'],
            ['slug' => 'delete.ProductionPlan', 'name' => 'حذف خطة إنتاجية',    'category' => 'الخطط الإنتاجية'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Attach read.ProductionPlan to "مدير فرع" role if it exists
        $readPerm = Permission::where('slug', 'read.ProductionPlan')->first();
        if ($readPerm) {
            $branchManagerRole = Role::where('name', 'مدير فرع')->first();
            if ($branchManagerRole) {
                $branchManagerRole->permissions()->syncWithoutDetaching([$readPerm->id]);
                $this->command->info('✓ Added read.ProductionPlan to role: مدير فرع');
            } else {
                $this->command->warn('Role "مدير فرع" not found. Assign the permission manually via the admin UI.');
            }
        }

        $this->command->info('✓ ProductionPlan permissions are ready.');
    }
}
