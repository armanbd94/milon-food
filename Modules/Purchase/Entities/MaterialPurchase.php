<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;

class MaterialPurchase extends BaseModel
{
    protected $table    = 'material_purchase';
    protected $fillable = ['purchase_id', 'material_id', 'qty', 'received', 'purchase_unit_id', 'net_unit_cost', 'old_cost', 'new_unit_cost', 
    'discount', 'tax_rate', 'tax', 'labor_cost', 'total'];
}
