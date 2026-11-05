<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AnnouncementAcknowledgment extends Model
{
    public $timestamps=false;
    protected $guarded=[];
    protected function casts(): array { return ['acknowledged_at'=>'datetime']; }
    public function announcement(){return $this->belongsTo(InternalAnnouncement::class,'internal_announcement_id');}
    public function employee(){return $this->belongsTo(Employee::class);}
}
