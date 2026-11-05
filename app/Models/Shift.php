<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = ['shift_name','start_time','end_time','status','grace_minutes','break_minutes','work_days','crosses_midnight','overtime_multiplier'];
    protected function casts(): array { return ['work_days'=>'array','crosses_midnight'=>'boolean','grace_minutes'=>'integer','break_minutes'=>'integer','overtime_multiplier'=>'decimal:2']; }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function assignments(){return $this->hasMany(ShiftAssignment::class);}
}
