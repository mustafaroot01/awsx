<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // إن لم يكن لديك بيانات، يمكنك إلغاء تفعيل الـ factory نهائياً هنا

        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'مدير النظام',
                'password' => bcrypt('00000000'),
                'email_verified_at' => now(),
            ]
        );
    }
}
