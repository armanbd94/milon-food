<?php

namespace Modules\SalesMen\Entities;

use App\Models\BaseModel;

class SalesmenProductTarget extends BaseModel
{
    protected $fillable = [
        'ptcode','salesmen_id','product_id','target_value','achieved_value','commission_rate','commission_earned',
        'target_month','closing_date','created_by','modified_by'
    ];
    
}
