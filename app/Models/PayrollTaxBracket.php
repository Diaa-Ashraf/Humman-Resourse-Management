<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PayrollTaxBracket extends Model
{
    protected $fillable=['lower_bound','upper_bound','rate_percent','sort_order','effective_from','effective_to'];
    protected function casts(): array{return ['lower_bound'=>'decimal:2','upper_bound'=>'decimal:2','rate_percent'=>'decimal:3','effective_from'=>'date','effective_to'=>'date'];}
}
