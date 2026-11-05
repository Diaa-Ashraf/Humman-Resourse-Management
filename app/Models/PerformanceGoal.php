<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PerformanceGoal extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['target_value'=>'decimal:2','actual_value'=>'decimal:2','manager_score'=>'decimal:2']; }
    public function review(){return $this->belongsTo(PerformanceReview::class,'performance_review_id');}
}
