<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JobOpening extends Model
{
    protected $fillable = ['title','branch_id','section_id','headcount','employment_type','status','closing_date','description','approval_status','requested_by','approved_by','approved_at','approval_notes'];
    protected $casts = ['closing_date' => 'date','approved_at'=>'datetime'];
    public function candidates() { return $this->hasMany(Candidate::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function section() { return $this->belongsTo(Section::class); }
}
