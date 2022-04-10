<?php

namespace Modules\SalesMen\Entities;

use App\Models\BaseModel;
use Modules\Location\Entities\Area;
use Modules\SalesMen\Entities\Salesmen;

class SalesmenArea extends BaseModel
{
    protected $table = 'salesmen_areas';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['salesmen_id','area_id'];

    public function salesmen()
    {
        return $this->belongsTo(Salesmen::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
