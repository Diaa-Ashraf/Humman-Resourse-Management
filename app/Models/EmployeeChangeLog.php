<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EmployeeChangeLog extends Model
{
    protected $fillable = ['employee_id','user_id','event','changes','notes'];
    protected $casts = ['changes' => 'array'];
    public function employee() { return $this->belongsTo(Employee::class); }
    public function user() { return $this->belongsTo(User::class); }
}
