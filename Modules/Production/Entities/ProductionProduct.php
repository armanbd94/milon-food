<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use Modules\Production\Entities\Production;
use Modules\Production\Entities\ProductionCoupon;

class ProductionProduct extends BaseModel
{

    protected $fillable = ['production_id', 'product_id', 'year', 'mfg_date', 'exp_date', 'base_unit_qty', 'per_unit_cost'];

    public function production()
    {
        return $this->belongsTo(Production::class,'production_id','id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class,'production_product_materials','production_product_id',
        'material_id','id','id')
        ->withPivot('id', 'unit_id','qty','cost','total','used_qty', 'damaged_qty', 'odd_qty')
        ->withTimeStamps(); 
    }

    // public function coupons()
    // {
    //     return $this->hasMany(ProductionCoupon::class,'production_product_id','id');
    // }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['pp.id' => 'desc'];
    //custom search column property
    protected $_batch_no; 
    protected $_warehouse_id; 
    protected $_start_date;  
    protected $_end_date;  

    //methods to set custom search property value
    public function setBatchNo($batch_no)
    {
        $this->_batch_no = $batch_no;
    }

    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }

    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }


    private function get_datatable_query()
    {

        $this->column_order = ['pp.id', 'p.batch_no', 'p.warehouse_id', 'pp.product_id','pp.mfg_date', 'pp.exp_date',
         'pro.base_unit_id','pp.base_unit_qty','pp.per_unit_cost'];
        
        
        $query = DB::table('production_products as pp')
        ->leftjoin('productions as p','pp.production_id','=','p.id')
        ->leftjoin('warehouses as w','p.warehouse_id','=','w.id')
        ->leftjoin('products as pro','pp.product_id','=','pro.id')
        ->leftjoin('units as u','pro.base_unit_id','=','u.id')
        ->where([['p.status',1],['p.production_status',3]])
        ->selectRaw('pp.*,p.batch_no,p.warehouse_id,w.name as warehouse_name,
        pro.name as product_name,pro.code as product_code,u.unit_name as base_unit_name,u.unit_code as base_unit_code');
        //search query
        if (!empty($this->_batch_no)) {
            $query->where('p.batch_no', $this->_batch_no);
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereDate('p.start_date','>=', $this->_start_date)
                 ->whereDate('p.end_date','<=', $this->_end_date);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('p.warehouse_id', $this->_warehouse_id);
        }

        //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }

    public function getDatatableList()
    {
        $query = $this->get_datatable_query();
        if ($this->lengthVlaue != -1) {
            $query->offset($this->startVlaue)->limit($this->lengthVlaue);
        }
        return $query->get();
    }

    public function count_filtered()
    {
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }

    public function count_all()
    {
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
