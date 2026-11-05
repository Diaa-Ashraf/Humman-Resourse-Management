<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SalaryComponent extends Model
{
    protected $fillable=['code','name','type','taxable','insurable','recurring','is_active'];
    protected function casts(): array{return ['taxable'=>'boolean','insurable'=>'boolean','recurring'=>'boolean','is_active'=>'boolean'];}
    public function employeeAmounts(){return $this->hasMany(EmployeeSalaryComponent::class);}
}
