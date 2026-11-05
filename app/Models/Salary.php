<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['payment_date'=>'date','paid_at'=>'datetime','earnings_breakdown'=>'array','deductions_breakdown'=>'array','calculation_snapshot'=>'array'];
    }

    public function payrollRun() { return $this->belongsTo(PayrollRun::class); }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
