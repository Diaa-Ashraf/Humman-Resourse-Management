<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AdvanceInstallment extends Model
{
    protected $guarded=[];
    protected function casts(): array{return ['due_date'=>'date','paid_date'=>'date','amount'=>'decimal:2'];}
    public function advance(){return $this->belongsTo(EmployeeAdvance::class,'employee_advance_id');}
    public function salary(){return $this->belongsTo(Salary::class);}
}
