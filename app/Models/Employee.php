<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','name','email','phone','section_id','branch_id','shift_id','status','hire_date','position','base_salary','employee_number','national_id','date_of_birth','gender','address','emergency_contact_name','emergency_contact_phone','education_level','employment_type','contract_end_date','manager_employee_id','termination_date','termination_reason','insurance_wage','insurance_enabled','tax_enabled','payroll_bank_name','payroll_bank_account'];

    protected function casts(): array
    {
        return ['hire_date'=>'date','date_of_birth'=>'date','contract_end_date'=>'date','termination_date'=>'date','base_salary'=>'decimal:2','insurance_wage'=>'decimal:2','insurance_enabled'=>'boolean','tax_enabled'=>'boolean'];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }
    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }
    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function manager() { return $this->belongsTo(self::class, 'manager_employee_id'); }
    public function directReports() { return $this->hasMany(self::class, 'manager_employee_id'); }
    public function changeLogs() { return $this->hasMany(EmployeeChangeLog::class)->latest(); }
    public function lifecycleTasks() { return $this->hasMany(EmployeeLifecycleTask::class); }
    public function documents() { return $this->hasMany(EmployeeDocument::class); }
    public function salaryComponents(){return $this->hasMany(EmployeeSalaryComponent::class);}
    public function advances(){return $this->hasMany(EmployeeAdvance::class);}
    public function shiftAssignments(){return $this->hasMany(ShiftAssignment::class);}
    public function leaveBalances(){return $this->hasMany(LeaveBalance::class);}
    public function user(){return $this->belongsTo(User::class);}
    public function requests(){return $this->hasMany(EmployeeRequest::class);}
    public function announcementAcknowledgments(){return $this->hasMany(AnnouncementAcknowledgment::class);}
    
    
}
