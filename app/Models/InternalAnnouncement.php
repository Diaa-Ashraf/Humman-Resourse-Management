<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InternalAnnouncement extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['requires_acknowledgment'=>'boolean','published_at'=>'datetime','expires_at'=>'datetime']; }
    public function branch(){return $this->belongsTo(Branch::class);}
    public function section(){return $this->belongsTo(Section::class);}
    public function creator(){return $this->belongsTo(User::class,'created_by');}
    public function acknowledgments(){return $this->hasMany(AnnouncementAcknowledgment::class);}
}
