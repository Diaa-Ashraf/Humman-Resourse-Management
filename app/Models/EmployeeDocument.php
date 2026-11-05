<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EmployeeDocument extends Model
{
    protected $fillable = ['employee_id','category','title','reference_number','issued_at','expires_at','notes','file_path','original_filename','mime_type','file_size'];
    protected $casts = ['issued_at' => 'date','expires_at' => 'date'];
    public function employee() { return $this->belongsTo(Employee::class); }
}
