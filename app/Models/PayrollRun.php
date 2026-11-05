<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PayrollRun extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['period_start'=>'date','period_end'=>'date','payment_date'=>'date','reviewed_at'=>'datetime','approved_at'=>'datetime','closed_at'=>'datetime','scope_branch_ids'=>'array']; }
    public function salaries(){return $this->hasMany(Salary::class);}
    public function events(){return $this->hasMany(PayrollRunEvent::class)->with('user')->orderBy('created_at');}
    public function creator(){return $this->belongsTo(User::class,'created_by');}
    public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');}
    public function approver(){return $this->belongsTo(User::class,'approved_by');}
    public function closer(){return $this->belongsTo(User::class,'closed_by');}
}
