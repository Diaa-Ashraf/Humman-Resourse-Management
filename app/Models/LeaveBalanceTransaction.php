<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LeaveBalanceTransaction extends Model
{
    protected $guarded=[];
    protected function casts(): array{return ['days'=>'decimal:2'];}
    public function balance(){return $this->belongsTo(LeaveBalance::class,'leave_balance_id');}
    public function leave(){return $this->belongsTo(Leave::class);}
}
