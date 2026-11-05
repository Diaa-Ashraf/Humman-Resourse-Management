<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingEnrollment extends Model
{
    protected $fillable = ['training_course_id','employee_id','status','score','completed_at','attended_hours','assessment_before','assessment_after','effectiveness_rating','feedback'];
    protected $casts = ['completed_at' => 'date','attended_hours'=>'decimal:2','assessment_before'=>'decimal:2','assessment_after'=>'decimal:2'];
    public function course() { return $this->belongsTo(TrainingCourse::class, 'training_course_id'); }
    public function employee() { return $this->belongsTo(Employee::class); }
}
