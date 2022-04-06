<?php

namespace Modules\SalesMen\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesmenMonthlyTarget extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\SalesMen\Database\factories\SalesmenMonthlyTargetFactory::new();
    }
}
