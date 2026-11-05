<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CompanyHoliday extends Model
{
    protected $fillable=['name','holiday_date','is_recurring','scope','branch_id'];
    protected function casts():array{return ['holiday_date'=>'date','is_recurring'=>'boolean'];}
    public function branch(){return $this->belongsTo(Branch::class);}
}
