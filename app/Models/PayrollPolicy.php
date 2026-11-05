<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PayrollPolicy extends Model
{
    protected $fillable=['key','label','value','unit','effective_from','effective_to','updated_by'];
    protected function casts(): array{return ['value'=>'decimal:4','effective_from'=>'date','effective_to'=>'date'];}
}
