<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'section_id', 'shift_id', 'status', 'hire_date', 'position', 'base_salary' ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }
    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }
    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
    
    
}
