<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EmployeeRequest extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['details'=>'array','reviewed_at'=>'datetime']; }
    public function employee(){return $this->belongsTo(Employee::class);}
    public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');}
}
