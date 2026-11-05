<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShiftAssignment extends Model
{
    protected $fillable=['employee_id','shift_id','work_date','status','approved_by','notes'];
    protected function casts(): array{return ['work_date'=>'date'];}
    public function employee(){return $this->belongsTo(Employee::class);}
    public function shift(){return $this->belongsTo(Shift::class);}
}
