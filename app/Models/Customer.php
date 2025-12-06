<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company',
        'industry_id',
        'status',
        'notes'
    ];

    protected $guarded = [];

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
