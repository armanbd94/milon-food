<?php

namespace Modules\Transfer\Entities;

use App\Models\Unit;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;

class TransferProduct extends Model
{

    protected $fillable = [
       'transfer_id','product_id','transfer_qty','receive_qty','damage_qty','unit_id','net_unit_cost','total'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
