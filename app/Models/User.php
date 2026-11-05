<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_secret' => 'encrypted',
            'mfa_enabled_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    public function employee() { return $this->hasOne(Employee::class); }
    public function branchScopes() { return $this->belongsToMany(Branch::class,'user_branch_scopes')->withPivot('assigned_by')->withTimestamps(); }
    public function securitySessions() { return $this->hasMany(UserSession::class); }
}
