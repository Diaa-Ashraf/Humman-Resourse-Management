<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeAdvance extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'requested_at' => 'date',
            'first_deduction_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function installmentRecords(): HasMany
    {
        return $this->hasMany(AdvanceInstallment::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(AdvanceInstallment::class);
    }
}
