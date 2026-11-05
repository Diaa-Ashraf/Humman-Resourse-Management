<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
            'branches' => 'الفروع',
            'sections' => 'الأقسام',
            'shifts' => 'الشيفتات',
            'attendances' => 'الحضور والانصراف',
            'leaves' => 'طلبات الإجازة',
            'leave_types' => 'أنواع الإجازات',
            'salaries' => 'المرتبات',
            'bonuses' => 'المكافآت',
            'deductions' => 'الخصومات',
            'reports' => 'التقارير',
            'recruitment' => 'التوظيف والمرشحون',
            'performance' => 'تقييم الأداء',
            'training' => 'التدريب والتطوير',
            'employee_documents' => 'مستندات الموظفين',
            'advances' => 'السلف والقروض',
        ];

        $actions = ['create', 'store', 'show', 'update', 'edit', 'delete', 'view'];
        $displayNames = [];

        $permissions = [];

        foreach ($modules as $table => $displayAr) {
            foreach ($actions as $action) {
                $displayNames["{$action}_{$table}"] = $this->getDisplayName($action, $displayAr);
                $permissions[] = [
                    'name' => "{$action}_{$table}",
                    'display_name' => $this->getDisplayName($action, $displayAr),
                    'table' => $table,
                ];
            }
        }

        foreach (['bonuses' => 'المكافآت', 'deductions' => 'الخصومات', 'leaves' => 'طلبات الإجازة', 'performance' => 'تقييمات الأداء', 'advances'=>'السلف والقروض'] as $table => $label) {
            $permissionName = 'review_' . $table;
            $displayName = 'اعتماد أو رفض ' . $label;
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['display_name' => $displayName, 'table' => $table]
            );
            $displayNames[$permissionName] = $displayName;
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['display_name' => $permission['display_name'], 'table' => $permission['table']]
            );
        }

        foreach (Permission::where('guard_name', 'web')->get() as $permission) {
            if (isset($displayNames[$permission->name])) {
                $permission->update(['display_name' => $displayNames[$permission->name]]);
            }
        }

        $permissionNames = array_merge(array_column($permissions, 'name'), ['review_bonuses', 'review_deductions', 'review_leaves', 'review_performance','review_advances']);
        foreach(['review_payroll'=>'مراجعة مسير الرواتب','approve_payroll'=>'اعتماد مسير الرواتب','close_payroll'=>'إقفال مسير الرواتب','manage_payroll_settings'=>'إعداد سياسات الرواتب والضرائب','manage_salary_components'=>'إدارة مكونات الرواتب','import_attendances'=>'استيراد سجلات الحضور','review_attendances'=>'مراجعة استثناءات الحضور','manage_shift_assignments'=>'إدارة جداول الورديات','manage_leave_balances'=>'إدارة أرصدة الإجازات','approve_recruitment'=>'اعتماد طلبات التوظيف','manage_internal_comms'=>'إدارة الإعلانات والسياسات','show_internal_comms'=>'عرض الإعلانات والسياسات','review_employee_requests'=>'مراجعة طلبات الموظفين','employee_portal'=>'بوابة الموظف الذاتية','approve_training_plans'=>'اعتماد خطط التدريب'] as $name=>$displayName){
            $table=match($name){'approve_recruitment'=>'recruitment','manage_internal_comms','show_internal_comms'=>'internal_comms','review_employee_requests'=>'employee_requests','employee_portal'=>'employees','approve_training_plans'=>'training',default=>str_contains($name,'attendance')?'attendances':(str_contains($name,'leave')?'leaves':'salaries')};
            Permission::firstOrCreate(['name'=>$name,'guard_name'=>'web'],['display_name'=>$displayName,'table'=>$table])->update(['display_name'=>$displayName,'table'=>$table]);
            $permissionNames[]=$name;
        }
        $payPermission = Permission::firstOrCreate(
            ['name' => 'pay_salaries', 'guard_name' => 'web'],
            ['display_name' => 'تأكيد صرف الرواتب', 'table' => 'salaries']
        );
        $payPermission->update(['display_name' => 'تأكيد صرف الرواتب']);
        $permissionNames[] = 'pay_salaries';
        $assignRolesPermission = Permission::firstOrCreate(
            ['name' => 'assign_roles', 'guard_name' => 'web'],
            ['display_name' => 'إسناد أدوار المستخدمين', 'table' => 'users']
        );
        $assignRolesPermission->update(['display_name' => 'إسناد أدوار المستخدمين']);
        $permissionNames[] = 'assign_roles';
        $manageUsersPermission = Permission::firstOrCreate(
            ['name' => 'manage_users', 'guard_name' => 'web'],
            ['display_name' => 'إدارة حسابات المستخدمين', 'table' => 'users']
        );
        $manageUsersPermission->update(['display_name' => 'إدارة حسابات المستخدمين']);
        $permissionNames[] = 'manage_users';
        foreach (['import_employees'=>'استيراد الموظفين','export_employees'=>'تصدير بيانات الموظفين'] as $name=>$displayName) {
            Permission::firstOrCreate(['name'=>$name,'guard_name'=>'web'],['display_name'=>$displayName,'table'=>'employees'])->update(['display_name'=>$displayName]);
            $permissionNames[]=$name;
        }
        $governancePermissions = [
            'view_all_branches' => 'عرض جميع الفروع',
            'manage_branch_scopes' => 'إدارة نطاقات الفروع للمستخدمين',
            'view_sensitive_employee_data' => 'عرض البيانات الشخصية والمالية الحساسة',
            'manage_security' => 'إدارة إعدادات الأمان والجلسات',
            'view_audit_logs' => 'عرض سجل التدقيق',
            'manage_retention' => 'إدارة سياسات الاحتفاظ بالبيانات',
        ];
        foreach ($governancePermissions as $name => $displayName) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'], ['display_name' => $displayName, 'table' => 'users'])
                ->update(['display_name' => $displayName]);
            $permissionNames[] = $name;
        }

        // -----------------------------
        // صلاحيات HR
        // -----------------------------
        $hrPermissions = collect($permissionNames)->filter(fn($perm) =>
            str_contains($perm, 'users') ||
            str_contains($perm, 'employees') ||
            str_contains($perm, 'attendances') ||
            str_contains($perm, 'leaves') ||
            str_contains($perm, 'leave_types') ||
            str_contains($perm, 'salaries') ||
            str_contains($perm, 'bonuses') ||
            str_contains($perm, 'deductions') || str_contains($perm, 'advances') ||
            str_contains($perm, 'shifts') ||
            str_contains($perm, 'sections')
            || str_contains($perm, 'branches') || str_contains($perm, 'reports')
            || str_contains($perm, 'recruitment') || str_contains($perm, 'performance') || str_contains($perm, 'training') || str_contains($perm, 'employee_documents')
        )->toArray();
        $hrPermissions[] = 'review_leaves';
        $hrPermissions[] = 'review_advances';
        $hrPermissions[] = 'review_attendances';
        $hrPermissions[] = 'import_attendances';
        $hrPermissions[] = 'manage_shift_assignments';
        $hrPermissions[] = 'manage_leave_balances';
        $hrPermissions[] = 'manage_salary_components';
        $hrPermissions[] = 'review_performance';
        array_push($hrPermissions,'approve_recruitment','manage_internal_comms','show_internal_comms','review_employee_requests','approve_training_plans');
        $hrPermissions[] = 'pay_salaries';
        $hrPermissions[] = 'import_employees';
        $hrPermissions[] = 'export_employees';
        $hrPermissions = array_values(array_diff($hrPermissions, ['assign_roles', 'show_users', 'view_users', 'create_users', 'store_users', 'update_users', 'edit_users', 'delete_users', 'create_branches', 'store_branches', 'update_branches', 'edit_branches', 'delete_branches', 'view_salaries', 'pay_salaries']));
        $hrPermissions[] = 'show_salaries';
        $hrPermissions[] = 'review_payroll';
        $hrPermissions[] = 'manage_salary_components';
        $hrPermissions[] = 'view_sensitive_employee_data';
        $hrPermissions = array_values(array_diff($hrPermissions, ['create_reports', 'store_reports', 'update_reports', 'edit_reports', 'delete_reports']));
        $hrPermissions = array_values(array_diff($hrPermissions, ['delete_users', 'manage_users']));

        // -----------------------------
        // صلاحيات Manager
        // -----------------------------
        $managerPermissions = collect($permissionNames)->filter(fn($perm) =>
            str_starts_with($perm, 'show_') || str_starts_with($perm, 'view_')
        )->toArray();
        $managerPermissions = array_values(array_diff($managerPermissions, ['show_salaries', 'view_salaries', 'show_users', 'view_users', 'show_reports', 'view_reports', 'show_employee_documents', 'view_employee_documents','show_advances','view_advances','view_all_branches','view_sensitive_employee_data','view_audit_logs']));
        $managerPermissions = array_values(array_diff($managerPermissions, ['review_payroll','approve_payroll','close_payroll','pay_salaries','show_internal_comms','approve_recruitment','approve_training_plans']));
        $managerPermissions[] = 'review_bonuses';
        $managerPermissions[] = 'review_deductions';
        $managerPermissions[] = 'review_leaves';
        $managerPermissions[] = 'review_performance';
        $managerPermissions[] = 'review_leaves';
        $managerPermissions[] = 'review_advances';
        $managerPermissions[] = 'show_internal_comms';
        $managerPermissions = array_values(array_unique($managerPermissions));

        // -----------------------------
        // صلاحيات Super Admin
        // -----------------------------
        $superAdminPermissions = array_merge($permissionNames, ['review_bonuses', 'review_deductions', 'review_leaves', 'review_performance', 'review_advances','review_payroll','approve_payroll','close_payroll','manage_payroll_settings','manage_salary_components','import_attendances','review_attendances','manage_shift_assignments','manage_leave_balances','pay_salaries','approve_recruitment','manage_internal_comms','show_internal_comms','review_employee_requests','approve_training_plans','manage_users','assign_roles','view_all_branches','manage_branch_scopes','view_sensitive_employee_data','manage_security','view_audit_logs','manage_retention']);

        // -----------------------------
        // إنشاء الأدوار
        // -----------------------------
        $this->createRole('HR', 'مسؤول الموارد البشرية', $hrPermissions);
        $this->createRole('Manager', 'المدير', $managerPermissions);
        $this->createRole('Super Admin', 'سوبر أدمن', $superAdminPermissions);
        $this->createRole('Employee Portal', 'بوابة الموظف', ['employee_portal','show_internal_comms']);
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
        foreach ($permissions as $permissionName) {
            if (!str_starts_with($permissionName, 'show_')) {
                continue;
            }
            $alias = str_replace('show_', 'view_', $permissionName);
            if (Permission::where('name', $alias)->exists()) {
                $role->givePermissionTo($alias);
            }
        }
        if (in_array('show_reports', $permissions, true) && Permission::where('name', 'view_reports')->exists()) {
            $role->givePermissionTo('view_reports');
        }
    }

}
