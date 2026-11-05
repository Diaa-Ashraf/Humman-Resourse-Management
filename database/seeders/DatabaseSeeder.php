<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionAndRoleSeeder::class);
        $this->call(PayrollPolicySeeder::class);
        $this->call(GovernancePolicySeeder::class);

        // إنشاء الحسابات الأساسية للوحة التحكم
        $adminEmail = env('HRMS_ADMIN_EMAIL', 'superadmin@gmail.com');
        $adminPassword = env('HRMS_ADMIN_PASSWORD', '12345678');
        $adminName = env('HRMS_ADMIN_NAME', 'Super Admin');

        if (!User::where('email', $adminEmail)->exists()) {
            $admin = User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
            ]);
            $adminRole = Role::findByName('Super Admin', 'web');
            $admin->assignRole($adminRole);
        }

        // حساب مسؤول الموارد البشرية التجريبي
        if (!User::where('email', 'hr@gmail.com')->exists()) {
            $hr = User::create([
                'name' => 'مسؤول الموارد البشرية',
                'email' => 'hr@gmail.com',
                'password' => Hash::make('12345678'),
            ]);
            $hrRole = Role::findByName('HR', 'web');
            $hr->assignRole($hrRole);
        }

        // حساب المدير التجريبي
        if (!User::where('email', 'manager@gmail.com')->exists()) {
            $manager = User::create([
                'name' => 'المدير المباشر',
                'email' => 'manager@gmail.com',
                'password' => Hash::make('12345678'),
            ]);
            $managerRole = Role::findByName('Manager', 'web');
            $manager->assignRole($managerRole);
        }
    }
}
