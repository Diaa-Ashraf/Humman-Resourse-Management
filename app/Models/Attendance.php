<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id','attendance_date','check_in','check_out','status','shift_id','late_minutes','overtime_minutes','source','review_status','reviewed_by','review_notes'];
    protected function casts(): array { return ['attendance_date'=>'date']; }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function shift(){return $this->belongsTo(Shift::class);}
    
}
