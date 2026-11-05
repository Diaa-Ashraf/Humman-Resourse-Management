<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\LeaveType;
use App\Models\Section;
use App\Models\Shift;
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

        // 1. إنشاء الحسابات الأساسية للوحة التحكم
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

        if (!User::where('email', 'hr@gmail.com')->exists()) {
            $hr = User::create([
                'name' => 'مسؤول الموارد البشرية',
                'email' => 'hr@gmail.com',
                'password' => Hash::make('12345678'),
            ]);
            $hrRole = Role::findByName('HR', 'web');
            $hr->assignRole($hrRole);
        }

        if (!User::where('email', 'manager@gmail.com')->exists()) {
            $manager = User::create([
                'name' => 'المدير المباشر',
                'email' => 'manager@gmail.com',
                'password' => Hash::make('12345678'),
            ]);
            $managerRole = Role::findByName('Manager', 'web');
            $manager->assignRole($managerRole);
        }

        // 2. الفروع الأساسية
        if (Branch::count() === 0) {
            $mainBranch = Branch::create([
                'name' => 'الفرع الرئيسي',
                'code' => 'HQ-01',
                'address' => 'المقر الرئيسي للشركة',
                'is_active' => true,
            ]);

            Branch::create([
                'name' => 'فرع القاهرة',
                'code' => 'CAI-01',
                'address' => 'مدينة نصر، القاهرة',
                'is_active' => true,
            ]);
        } else {
            $mainBranch = Branch::first();
        }

        // 3. الأقسام الأساسية
        if (Section::count() === 0) {
            foreach ([
                ['إدارة الموارد البشرية', 'HR-01'],
                ['الإدارة المالية والحسابات', 'FIN-01'],
                ['إدارة تقنية المعلومات', 'IT-01'],
                ['إدارة المبيعات والتسويق', 'SALES-01'],
                ['الإدارة العامة والتشغيل', 'OPS-01'],
            ] as [$secName, $secCode]) {
                Section::create([
                    'name' => $secName,
                    'code' => $secCode,
                    'branch_id' => $mainBranch?->id,
                    'is_active' => true,
                ]);
            }
        }

        // 4. الورديات الأساسية
        if (Shift::count() === 0) {
            Shift::create([
                'shift_name' => 'الوردية الصباحية (9:00 ص - 5:00 م)',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'open',
                'grace_minutes' => 15,
                'break_minutes' => 60,
                'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'overtime_multiplier' => 1.5,
            ]);

            Shift::create([
                'shift_name' => 'الوردية المسائية (5:00 م - 1:00 ص)',
                'start_time' => '17:00:00',
                'end_time' => '01:00:00',
                'status' => 'open',
                'grace_minutes' => 15,
                'break_minutes' => 60,
                'crosses_midnight' => true,
                'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'overtime_multiplier' => 1.5,
            ]);
        }

        // 5. أنواع الإجازات الأساسية
        if (LeaveType::count() === 0) {
            LeaveType::create(['name' => 'إجازة سنوية', 'max_days' => 21, 'status' => 'active', 'description' => 'الرصيد السنوي الاعتيادي']);
            LeaveType::create(['name' => 'إجازة مرضية', 'max_days' => 14, 'status' => 'active', 'description' => 'تقرير طبي معتمد']);
            LeaveType::create(['name' => 'إجازة عارضة', 'max_days' => 7, 'status' => 'active', 'description' => 'ظروف طارئة بحد أقصى يومين متتاليين']);
        }
    }
}
