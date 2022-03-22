<?php

namespace Modules\DamageProduct\Entities;

use App\Models\BaseModel;
use Modules\Product\Entities\Product;

class DamageProduct extends BaseModel
{
    protected $fillable = ['damage_id', 'memo_no', 'product_id', 'return_qty', 'unit_id', 'product_rate', 
    'deduction_rate', 'deduction_amount', 'total'];

     public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
