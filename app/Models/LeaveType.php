<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'max_days',
        'status',
        'annual_entitlement_days','exclude_weekends','requires_coverage','max_consecutive_days','carryover_cap_days',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
    public function balances(){return $this->hasMany(LeaveBalance::class);}
}
