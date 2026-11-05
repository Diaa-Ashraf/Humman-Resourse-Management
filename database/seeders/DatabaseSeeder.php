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

        // Optional first administrator provisioning for installations with no existing account.
        $email = env('HRMS_ADMIN_EMAIL');
        $password = env('HRMS_ADMIN_PASSWORD');
        if ($email && $password && !User::where('email', $email)->exists() && User::count() < 10) {
            $admin = User::create([
                'name' => env('HRMS_ADMIN_NAME', 'مدير النظام'),
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $admin->assignRole(Role::findByName('Super Admin', 'web'));
        }
    }
}
