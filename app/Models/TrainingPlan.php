<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingPlan extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['budget'=>'decimal:2','approved_at'=>'datetime']; }
    public function branch(){return $this->belongsTo(Branch::class);}
    public function courses(){return $this->hasMany(TrainingCourse::class);}
    public function needs(){return $this->hasMany(TrainingNeed::class);}
    public function creator(){return $this->belongsTo(User::class,'created_by');}
    public function approver(){return $this->belongsTo(User::class,'approved_by');}
}
