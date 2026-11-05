<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UserSession extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['last_seen_at'=>'datetime','revoked_at'=>'datetime']; }
    public function user(){return $this->belongsTo(User::class);}
}
