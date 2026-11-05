<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingCourse extends Model
{
    protected $fillable = ['title','provider','duration_hours','cost','starts_at','ends_at','status','description','training_plan_id','branch_id'];
    protected $casts = ['starts_at' => 'date','ends_at' => 'date'];
    public function enrollments() { return $this->hasMany(TrainingEnrollment::class); }
    public function employees() { return $this->belongsToMany(Employee::class, 'training_enrollments')->withPivot(['status','score','completed_at'])->withTimestamps(); }
    public function plan() { return $this->belongsTo(TrainingPlan::class, 'training_plan_id'); }
}
