<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingNeed extends Model
{
    protected $guarded=[];
    public function plan(){return $this->belongsTo(TrainingPlan::class,'training_plan_id');}
    public function employee(){return $this->belongsTo(Employee::class);}
    public function section(){return $this->belongsTo(Section::class);}
}
