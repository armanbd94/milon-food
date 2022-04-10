<?php

namespace Modules\SalesMen\Entities;

use App\Models\BaseModel;

class SalesmenMonthlyTarget extends BaseModel
{
    protected $fillable = [
        'mtcode','salesmen_id','target_value','achieved_value','commission_rate','commission_earned','target_month','closing_date','created_by','modified_by'
    ];
    
}
