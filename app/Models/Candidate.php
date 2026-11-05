<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Candidate extends Model
{
    protected $fillable = ['job_opening_id','name','email','phone','source','status','notes','offer_salary','offer_status','offer_sent_at','offer_response_date','offer_decided_at','hired_employee_id'];
    protected $casts = ['offer_salary'=>'decimal:2','offer_sent_at'=>'datetime','offer_response_date'=>'date','offer_decided_at'=>'datetime'];
    public function opening() { return $this->belongsTo(JobOpening::class, 'job_opening_id'); }
    public function interviews() { return $this->hasMany(CandidateInterview::class)->latest('scheduled_at'); }
    public function hiredEmployee() { return $this->belongsTo(Employee::class, 'hired_employee_id'); }
}
