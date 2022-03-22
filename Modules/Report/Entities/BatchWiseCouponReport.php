<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class BatchWiseCouponReport extends BaseModel
{
    protected $table="production_products";

    protected $guarded = [];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['pp.id' => 'asc'];
    //custom search column property
    protected $_batch_no; 
    protected $_product_id; 


    //methods to set custom search property value
    public function setBatchNo($batch_no)
    {
        $this->_batch_no = $batch_no;
    }
    public function setProductID($product_id)
    {
        $this->_product_id = $product_id;
    }

    private function get_datatable_query()
    {

        $this->column_order = ['pp.id','pro.warehouse_id','pro.batch_no','pp.product_id','pp.total_coupon',null];
        
        $query = DB::table('production_products as pp')
        ->leftJoin('productions as pro','pp.production_id','=','pro.id')
        ->leftJoin('warehouses as w','pro.warehouse_id','=','w.id')
        ->leftJoin('products as p','pp.product_id','=','p.id')
        ->select('pp.*','pro.batch_no','w.name as warehouse_name','p.name as product_name',DB::raw('(select count(pc.id) from production_coupons
         as pc where pc.production_product_id = pp.id AND pc.status = 1 GROUP BY pc.production_product_id) as total_used_coupons'))
         ->where([['pp.has_coupon',1],['pro.status',1],['pro.production_status',3],['pro.transfer_status',2]]);

        //search query
        if (!empty($this->_batch_no)) {
            $query->where('pro.batch_no', $this->_batch_no);
        }
        if (!empty($this->_product_id)) {
            $query->where('pp.product_id', $this->_product_id);
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
        return DB::table('production_products as pp')
        ->leftJoin('productions as pro','pp.production_id','=','pro.id')
        ->leftJoin('warehouses as w','pro.warehouse_id','=','w.id')
        ->leftJoin('products as p','pp.product_id','=','p.id')
        ->select('pp.*','pro.batch_no','w.name as warehouse_name','p.name as product_name',DB::raw('(select count(pc.id) from production_coupons
         as pc where pc.production_product_id = pp.id AND pc.status = 1 GROUP BY pc.production_product_id) as total_used_coupons'))
         ->where([['pp.has_coupon',1],['pro.status',1],['pro.production_status',3],['pro.transfer_status',2]])
         ->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
