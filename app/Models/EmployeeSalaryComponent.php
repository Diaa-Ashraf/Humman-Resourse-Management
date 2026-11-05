<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EmployeeSalaryComponent extends Model
{
    protected $fillable=['employee_id','salary_component_id','amount','effective_from','effective_to'];
    protected function casts(): array{return ['amount'=>'decimal:2','effective_from'=>'date','effective_to'=>'date'];}
    public function employee(){return $this->belongsTo(Employee::class);}
    public function component(){return $this->belongsTo(SalaryComponent::class,'salary_component_id');}
}
