<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CandidateInterview extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['scheduled_at'=>'datetime','rating'=>'decimal:1']; }
    public function candidate(){return $this->belongsTo(Candidate::class);}
    public function interviewer(){return $this->belongsTo(User::class,'interviewer_id');}
}
