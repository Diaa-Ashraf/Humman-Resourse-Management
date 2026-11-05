<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PerformanceReview extends Model
{
    protected $fillable = ['employee_id','reviewer_id','period_start','period_end','rating','strengths','improvement_areas','goals','status','employee_comments','manager_comments','submitted_at','approved_by'];
    protected $casts = ['period_start' => 'date','period_end' => 'date','submitted_at'=>'datetime'];
    public function employee() { return $this->belongsTo(Employee::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function performanceGoals() { return $this->hasMany(PerformanceGoal::class); }
}
