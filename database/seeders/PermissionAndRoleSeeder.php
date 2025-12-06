<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionAndRoleSeeder extends Seeder
{
    public function run(): void
    {
        // الموديولات الأساسية + الترجمة
        $modules = [
            'users' => 'المستخدمين',
            'employees' => 'الموظفين',
            'sections' => 'الأقسام',
            'shifts' => 'الشيفتات',
            'attendances' => 'الحضور والانصراف',
            'leaves' => 'طلبات الإجازة',
            'leave_types' => 'أنواع الإجازات',
            'salaries' => 'المرتبات',
            'bonuses' => 'المكافآت',
            'deductions' => 'الخصومات',
            'reports' => 'التقارير',

            // الموديولات الجديدة للسيلز
            'leads' => 'العملاء المحتملين',
            'customers' => 'العملاء',
            'visits' => 'الزيارات',
            'industries' => 'القطاعات',
        ];

        $actions = ['create', 'store', 'show', 'update', 'edit', 'delete'];

        $permissions = [];

        foreach ($modules as $table => $displayAr) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'name' => "{$action}_{$table}",
                    'display_name' => $this->getDisplayName($action, $displayAr),
                    'table' => $table,
                ];
            }
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['display_name' => $permission['display_name'], 'table' => $permission['table']]
            );
        }

        $permissionNames = array_column($permissions, 'name');

        // -----------------------------
        // صلاحيات HR (بدون Sales)
        // -----------------------------
        $hrPermissions = collect($permissionNames)->filter(fn($perm) =>
            str_contains($perm, 'employees') ||
            str_contains($perm, 'attendances') ||
            str_contains($perm, 'leaves') ||
            str_contains($perm, 'leave_types') ||
            str_contains($perm, 'salaries') ||
            str_contains($perm, 'bonuses') ||
            str_contains($perm, 'deductions') ||
            str_contains($perm, 'shifts') ||
            str_contains($perm, 'sections')
        )->toArray();

        // -----------------------------
        // صلاحيات Sales Team
        // -----------------------------
        $salesPermissions = collect($permissionNames)->filter(function ($perm) {
            return
                str_contains($perm, 'leads') ||
                str_contains($perm, 'customers') ||
                str_contains($perm, 'visits') ||
                str_contains($perm, 'industries');
        })->toArray();

        // الزيارات عادة لا تكون CRUD كامل → نخليهم بدون delete
        $salesPermissions = array_filter($salesPermissions, fn($p) => !str_starts_with($p, 'delete_visits'));

        // -----------------------------
        // صلاحيات Manager
        // -----------------------------
        $managerPermissions = collect($permissionNames)->filter(fn($perm) =>
            str_starts_with($perm, 'show_') ||
            str_contains($perm, 'reports')
        )->toArray();

        // -----------------------------
        // صلاحيات Super Admin
        // -----------------------------
        $superAdminPermissions = $permissionNames;

        // -----------------------------
        // إنشاء الأدوار
        // -----------------------------
        $this->createRole('HR', 'مسؤول الموارد البشرية', $hrPermissions);
        $this->createRole('Sales', 'فريق المبيعات', $salesPermissions);
        $this->createRole('Manager', 'المدير', $managerPermissions);
        $this->createRole('Super Admin', 'سوبر أدمن', $superAdminPermissions);

        // -----------------------------
        // إنشاء المستخدمين للمثال
        // -----------------------------
        $this->createUser('hr@gmail.com', 'HR User', 'password123', 'HR');
        $this->createUser('sales@gmail.com', 'Sales User', 'password123', 'Sales');
        $this->createUser('manager@gmail.com', 'Manager', 'password123', 'Manager');
        $this->createUser('superadmin@gmail.com', 'Super Admin', 'password123', 'Super Admin');
    }

    private function getDisplayName($action, $name)
    {
        return match ($action) {
            'create' => 'إنشاء ' . $name,
            'show' => 'عرض ' . $name,
            'edit' => 'تعديل ' . $name,
            'delete' => 'حذف ' . $name,
            default => $name
        };
    }

    private function createRole($name, $display_name, $permissions)
    {
        $role = Role::firstOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            ['display_name' => $display_name]
        );

        $role->syncPermissions($permissions);
    }

    private function createUser($email, $name, $password, $roleName)
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt($password)]
        );

        $user->assignRole($roleName);
    }
}
