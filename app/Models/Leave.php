<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'days_count','coverage_employee_id','reviewed_by','reviewed_at','review_notes',
    ];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date','days_count'=>'decimal:2','reviewed_at'=>'datetime']; }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
    public function coverageEmployee(){return $this->belongsTo(Employee::class,'coverage_employee_id');}
    public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');}
    
}
