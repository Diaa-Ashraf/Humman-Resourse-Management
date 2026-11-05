<?php

use App\Models\{Branch, Employee, Section, Shift, User};
use App\Services\BranchAccess;
use App\Services\Totp;
use Database\Seeders\PermissionAndRoleSeeder;
use Illuminate\Support\Facades\Validator;
use App\Rules\StrongPassword;

function makeGovernanceEmployee(Branch $branch, string $suffix): Employee
{
    $section=Section::create(['name'=>'قسم '.$suffix,'branch_id'=>$branch->id,'code'=>'SEC'.$suffix,'is_active'=>true]);
    $shift=Shift::create(['shift_name'=>'وردية '.$suffix,'start_time'=>'08:00','end_time'=>'16:00','status'=>'open']);
    return Employee::create(['name'=>'موظف '.$suffix,'email'=>$suffix.'@example.test','phone'=>'01000000000','position'=>'عامل','section_id'=>$section->id,'branch_id'=>$branch->id,'shift_id'=>$shift->id,'status'=>'active','hire_date'=>today(),'base_salary'=>8000,'employment_type'=>'دوام كامل','insurance_enabled'=>false,'tax_enabled'=>false]);
}

it('limits an assigned account to employees in its approved branch scope', function () {
    $branchA=Branch::create(['name'=>'فرع أ','code'=>'A01','is_active'=>true]);
    $branchB=Branch::create(['name'=>'فرع ب','code'=>'B01','is_active'=>true]);
    $employeeA=makeGovernanceEmployee($branchA,'A');
    $employeeB=makeGovernanceEmployee($branchB,'B');
    $user=User::factory()->create();
    $user->branchScopes()->attach($branchA->id,['assigned_by'=>null]);

    expect(app(BranchAccess::class)->employees(Employee::query(),$user)->pluck('id')->all())->toBe([$employeeA->id]);
    expect(fn()=>app(BranchAccess::class)->assertEmployee($employeeB,$user))->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('does not give managers global or sensitive-data permissions', function () {
    $this->seed(PermissionAndRoleSeeder::class);
    $manager=Spatie\Permission\Models\Role::findByName('Manager','web');

    expect($manager->hasPermissionTo('view_all_branches'))->toBeFalse();
    expect($manager->hasPermissionTo('view_sensitive_employee_data'))->toBeFalse();
    expect($manager->hasPermissionTo('show_salaries'))->toBeFalse();
});

it('verifies time-based one-time codes and rejects weak passwords', function () {
    $totp=app(Totp::class);$secret=$totp->secret();$timestamp=2_000_000_000;$code=$totp->code($secret,(int)floor($timestamp/30));
    expect($totp->verify($secret,$code,$timestamp))->toBeTrue();
    expect($totp->verify($secret,'invalid',$timestamp))->toBeFalse();

    $validator=Validator::make(['password'=>'short'],['password'=>[new StrongPassword]]);
    expect($validator->fails())->toBeTrue();
});
