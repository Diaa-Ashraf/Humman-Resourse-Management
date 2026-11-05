<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRunEvent extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['details' => 'array', 'created_at' => 'datetime']; }
    public function run() { return $this->belongsTo(PayrollRun::class, 'payroll_run_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
