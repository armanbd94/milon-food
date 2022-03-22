<?php

namespace Modules\Production\Entities;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Modules\Material\Entities\Material;
use Modules\Production\Entities\ProductionProduct;

class ProductionProductMaterial extends Model
{
    protected $fillable = ['production_product_id', 'material_id', 'unit_id','qty','cost','total', 'used_qty', 'damaged_qty', 'odd_qty'];

    public function production_product()
    {
        return $this->belongsTo(ProductionProduct::class,'production_product_id','id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class,'material_id','id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id','id');
    }
}
