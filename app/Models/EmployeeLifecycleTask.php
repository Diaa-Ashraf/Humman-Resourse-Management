<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EmployeeLifecycleTask extends Model
{
    protected $fillable = ['employee_id','assigned_to','phase','title','notes','due_date','completed_at','completed_by'];
    protected $casts = ['due_date'=>'date','completed_at'=>'datetime'];
    public function employee() { return $this->belongsTo(Employee::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
}
