<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LeaveBalance extends Model
{
    protected $guarded=[];
    protected function casts(): array{return ['opening_balance'=>'decimal:2','accrued_days'=>'decimal:2','used_days'=>'decimal:2','carried_days'=>'decimal:2'];}
    public function employee(){return $this->belongsTo(Employee::class);}
    public function leaveType(){return $this->belongsTo(LeaveType::class);}
    public function transactions(){return $this->hasMany(LeaveBalanceTransaction::class);}
    public function getAvailableAttribute(){return (float)$this->opening_balance+(float)$this->accrued_days+(float)$this->carried_days-(float)$this->used_days;}
}
