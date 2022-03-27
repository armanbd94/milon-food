<?php

namespace Modules\Dealer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealerProduct extends Model
{
    use HasFactory;

    protected $fillable = ['dealer_id','product_id','commission_amount'];


}
