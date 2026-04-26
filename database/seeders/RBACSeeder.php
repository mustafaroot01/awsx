<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RBACSeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks to truncate
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $permissions = [
            // Employees
            ['name' => 'عرض الموظفين', 'slug' => 'view_employees', 'category' => 'إدارة الموظفين'],
            ['name' => 'إضافة موظف جديد', 'slug' => 'add_employee', 'category' => 'إدارة الموظفين'],
            ['name' => 'تعديل بيانات موظف', 'slug' => 'edit_employee', 'category' => 'إدارة الموظفين'],
            ['name' => 'حذف موظف', 'slug' => 'delete_employee', 'category' => 'إدارة الموظفين'],
            ['name' => 'تصدير بيانات الموظفين', 'slug' => 'export_employees', 'category' => 'إدارة الموظفين'],

            // Evaluations
            ['name' => 'عرض التقييمات', 'slug' => 'view_evaluations', 'category' => 'التقييمات الدورية'],
            ['name' => 'بدء تقييم جديد', 'slug' => 'add_evaluation', 'category' => 'التقييمات الدورية'],
            ['name' => 'تعديل تقييم قائم', 'slug' => 'edit_evaluation', 'category' => 'التقييمات الدورية'],
            ['name' => 'حذف تقييم', 'slug' => 'delete_evaluation', 'category' => 'التقييمات الدورية'],
            ['name' => 'اعتماد التقييم نهائياً', 'slug' => 'approve_evaluation', 'category' => 'التقييمات الدورية'],
            ['name' => 'طباعة استمارة التقييم', 'slug' => 'print_evaluation', 'category' => 'التقييمات الدورية'],

            // Branches
            ['name' => 'عرض الفروع', 'slug' => 'view_branches', 'category' => 'إدارة الفروع'],
            ['name' => 'إضافة فرع جديد', 'slug' => 'add_branch', 'category' => 'إدارة الفروع'],
            ['name' => 'تعديل بيانات فرع', 'slug' => 'edit_branch', 'category' => 'إدارة الفروع'],
            ['name' => 'حذف فرع', 'slug' => 'delete_branch', 'category' => 'إدارة الفروع'],

            // User Management
            ['name' => 'عرض قائمة المستخدمين', 'slug' => 'view_users', 'category' => 'إدارة المستخدمين'],
            ['name' => 'إضافة مستخدم جديد', 'slug' => 'add_user', 'category' => 'إدارة المستخدمين'],
            ['name' => 'تعديل بيانات مستخدم', 'slug' => 'edit_user', 'category' => 'إدارة المستخدمين'],
            ['name' => 'حذف مستخدم', 'slug' => 'delete_user', 'category' => 'إدارة المستخدمين'],
            ['name' => 'تغيير كلمة مرور مستخدم', 'slug' => 'change_password', 'category' => 'إدارة المستخدمين'],

            // Roles & Permissions
            ['name' => 'عرض المجموعات', 'slug' => 'view_roles', 'category' => 'المجموعات والصلاحيات'],
            ['name' => 'إنشاء مجموعة جديدة', 'slug' => 'add_role', 'category' => 'المجموعات والصلاحيات'],
            ['name' => 'تعديل صلاحيات مجموعة', 'slug' => 'edit_role', 'category' => 'المجموعات والصلاحيات'],
            ['name' => 'حذف مجموعة', 'slug' => 'delete_role', 'category' => 'المجموعات والصلاحيات'],

            // System Settings
            ['name' => 'الوصول لإعدادات النظام', 'slug' => 'view_settings', 'category' => 'إعدادات النظام'],
            ['name' => 'تعديل إعدادات النظام', 'slug' => 'edit_settings', 'category' => 'إعدادات النظام'],
            ['name' => 'مشاهدة سجل العمليات (Logs)', 'slug' => 'view_logs', 'category' => 'إعدادات النظام'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Re-sync General Manager (Admin) with all new permissions
        $adminRole = Role::where('name', 'المدير العام')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::all()->pluck('id'));
        }
    }
}
