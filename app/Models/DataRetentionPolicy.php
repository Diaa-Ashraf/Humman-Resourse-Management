<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DataRetentionPolicy extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['cleanup_enabled'=>'boolean','retention_days'=>'integer']; }
    public function updater(){return $this->belongsTo(User::class,'updated_by');}
}
