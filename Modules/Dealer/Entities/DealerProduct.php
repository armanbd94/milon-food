<?php

namespace Modules\Dealer\Entities;

use Modules\Dealer\Entities\Dealer;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealerProduct extends Model
{
    use HasFactory;

    protected $fillable = ['dealer_id','product_id','commission_rate','commission_percentage'];

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
