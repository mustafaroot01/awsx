<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RBACSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permission_role')->truncate();
        Permission::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ─────────────────────────────────────────────────────────
        // صيغة الـ slug الديناميكية: "action.Subject"
        // auto-parse في AuthController: explode('.', $slug) → [action, subject]
        // ─────────────────────────────────────────────────────────
        $permissions = [

            // ── وثائق التأمين ─────────────────────────────────────
            ['name' => 'عرض الوثائق',           'slug' => 'read.Policy',          'category' => 'وثائق التأمين'],
            ['name' => 'إضافة وثيقة',            'slug' => 'create.Policy',        'category' => 'وثائق التأمين'],
            ['name' => 'تعديل وثيقة',            'slug' => 'update.Policy',        'category' => 'وثائق التأمين'],
            ['name' => 'حذف وثيقة',              'slug' => 'delete.Policy',        'category' => 'وثائق التأمين'],
            ['name' => 'طباعة وثيقة',            'slug' => 'print.Policy',         'category' => 'وثائق التأمين'],

            // ── الخطط الإنتاجية ──────────────────────────────────
            ['name' => 'عرض الخطط الإنتاجية',   'slug' => 'read.ProductionPlan',  'category' => 'الخطط الإنتاجية'],
            ['name' => 'إضافة خطة إنتاجية',      'slug' => 'create.ProductionPlan','category' => 'الخطط الإنتاجية'],
            ['name' => 'تعديل خطة إنتاجية',      'slug' => 'update.ProductionPlan','category' => 'الخطط الإنتاجية'],
            ['name' => 'حذف خطة إنتاجية',        'slug' => 'delete.ProductionPlan','category' => 'الخطط الإنتاجية'],

            // ── الإحصائيات والتقارير ─────────────────────────────
            ['name' => 'عرض الإحصائيات والتقارير','slug' => 'read.Statistics',    'category' => 'الإحصائيات والتقارير'],

            // ── الفروع ───────────────────────────────────────────
            ['name' => 'عرض الفروع',             'slug' => 'read.Branch',          'category' => 'إدارة الفروع'],
            ['name' => 'إضافة فرع',              'slug' => 'create.Branch',        'category' => 'إدارة الفروع'],
            ['name' => 'تعديل فرع',              'slug' => 'update.Branch',        'category' => 'إدارة الفروع'],
            ['name' => 'حذف فرع',                'slug' => 'delete.Branch',        'category' => 'إدارة الفروع'],

            // ── الموظفون ─────────────────────────────────────────
            ['name' => 'عرض الموظفين',           'slug' => 'read.Employee',        'category' => 'إدارة الموظفين'],
            ['name' => 'إضافة موظف',             'slug' => 'create.Employee',      'category' => 'إدارة الموظفين'],
            ['name' => 'تعديل موظف',             'slug' => 'update.Employee',      'category' => 'إدارة الموظفين'],
            ['name' => 'حذف موظف',               'slug' => 'delete.Employee',      'category' => 'إدارة الموظفين'],

            // ── التقييمات ────────────────────────────────────────
            ['name' => 'عرض التقييمات',          'slug' => 'read.Evaluation',      'category' => 'التقييمات الدورية'],
            ['name' => 'إضافة تقييم',            'slug' => 'create.Evaluation',    'category' => 'التقييمات الدورية'],
            ['name' => 'تعديل تقييم',            'slug' => 'update.Evaluation',    'category' => 'التقييمات الدورية'],
            ['name' => 'حذف تقييم',              'slug' => 'delete.Evaluation',    'category' => 'التقييمات الدورية'],
            ['name' => 'طباعة تقييم',            'slug' => 'print.Evaluation',     'category' => 'التقييمات الدورية'],

            // ── المستخدمون ──────────────────────────────────────
            ['name' => 'عرض المستخدمين',         'slug' => 'read.User',            'category' => 'إدارة المستخدمين'],
            ['name' => 'إضافة مستخدم',           'slug' => 'create.User',          'category' => 'إدارة المستخدمين'],
            ['name' => 'تعديل مستخدم',           'slug' => 'update.User',          'category' => 'إدارة المستخدمين'],
            ['name' => 'حذف مستخدم',             'slug' => 'delete.User',          'category' => 'إدارة المستخدمين'],

            // ── الأدوار والصلاحيات ───────────────────────────────
            ['name' => 'عرض الأدوار',            'slug' => 'read.Role',            'category' => 'الأدوار والصلاحيات'],
            ['name' => 'إضافة دور',              'slug' => 'create.Role',          'category' => 'الأدوار والصلاحيات'],
            ['name' => 'تعديل دور',              'slug' => 'update.Role',          'category' => 'الأدوار والصلاحيات'],
            ['name' => 'حذف دور',                'slug' => 'delete.Role',          'category' => 'الأدوار والصلاحيات'],

            // ── إعدادات النظام ────────────────────────────────────
            ['name' => 'إدارة إعدادات النظام',   'slug' => 'manage.Settings',      'category' => 'إعدادات النظام'],
            ['name' => 'عرض سجل النشاطات',       'slug' => 'read.Log',             'category' => 'إعدادات النظام'],

            // ── أساسي (محمي - لا يُزال من الصلاحية الافتراضية) ──
            ['name' => 'تسجيل الدخول وعرض الداشبورد', 'slug' => 'read.Auth', 'category' => 'أساسي'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // ─────────────────────────────────────────────────────────
        // الأدوار الافتراضية
        // ─────────────────────────────────────────────────────────
        $allIds = Permission::pluck('id');

        $roles = [
            // ── إدارة النظام ──────────────────── محمي تماماً / كل شيء
            [
                'name'        => 'إدارة النظام',
                'description' => 'صلاحية مطلقة محمية - لا يمكن تعديلها أو حذفها',
                'permissions' => $allIds,
            ],
            // ── الصلاحية الافتراضية ───────────── للمستخدمين بدون دور
            [
                'name'        => 'الصلاحية الافتراضية',
                'description' => 'صلاحية تُمنح تلقائياً لكل مستخدم جديد - read.Auth محمية',
                'permissions' => Permission::whereIn('slug', ['read.Auth'])->pluck('id'),
            ],
            // ── مدير عام ─────────────────────── كل شيء
            [
                'name'        => 'مدير عام',
                'description' => 'صلاحية كاملة على جميع أقسام النظام',
                'permissions' => $allIds,
            ],
            // ── مدير فرع ─────────────────────── وثائق فرعه + إحصائيات + خطط (قراءة)
            [
                'name'        => 'مدير فرع',
                'description' => 'إدارة وثائق الفرع ومتابعة الخطط والإحصائيات',
                'permissions' => Permission::whereIn('slug', [
                    'read.Policy', 'create.Policy', 'update.Policy', 'print.Policy',
                    'read.ProductionPlan',
                    'read.Statistics',
                    'read.Branch',
                    'read.Employee',
                    'read.Evaluation', 'create.Evaluation', 'update.Evaluation',
                ])->pluck('id'),
            ],
            // ── موظف وثائق ───────────────────── إضافة وتعديل وطباعة وثائق فقط
            [
                'name'        => 'موظف وثائق',
                'description' => 'إدخال وتعديل وطباعة وثائق التأمين',
                'permissions' => Permission::whereIn('slug', [
                    'read.Policy', 'create.Policy', 'update.Policy', 'print.Policy',
                    'read.ProductionPlan',
                ])->pluck('id'),
            ],
            // ── مدير موارد بشرية ─────────────── موظفون + تقييمات
            [
                'name'        => 'مدير موارد بشرية',
                'description' => 'إدارة الموظفين والتقييمات الدورية',
                'permissions' => Permission::whereIn('slug', [
                    'read.Employee', 'create.Employee', 'update.Employee', 'delete.Employee',
                    'read.Evaluation', 'create.Evaluation', 'update.Evaluation', 'delete.Evaluation', 'print.Evaluation',
                    'read.Branch',
                    'read.Statistics',
                ])->pluck('id'),
            ],
            // ── مشاهد ────────────────────────── قراءة فقط
            [
                'name'        => 'مشاهد',
                'description' => 'عرض البيانات بدون أي تعديل',
                'permissions' => Permission::whereIn('slug', [
                    'read.Policy',
                    'read.ProductionPlan',
                    'read.Statistics',
                    'read.Branch',
                    'read.Employee',
                ])->pluck('id'),
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                ['description' => $roleData['description']]
            );
            $role->permissions()->sync($roleData['permissions']);
        }
    }
}
