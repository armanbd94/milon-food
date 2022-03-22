<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use App\Models\Warehouse;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\StockReturn\Entities\SaleReturnProduct;


class DamageReport extends BaseModel
{
    protected $table = 'sale_returns';
    protected $fillable = ['return_no', 'memo_no', 'warehouse_id', 'customer_id', 'total_price', 'total_deduction', 
    'tax_rate', 'total_tax', 'grand_total', 'reason', 'date', 'return_date', 'created_by', 'modified_by'];
    
     public function sale()
     {
         return $this->belongsTo(Sale::class,'memo_no','memo_no');
     }

     public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
    
     public function customer()
     {
         return $this->belongsTo(Customer::class,'customer_id','id')->withDefault(['name'=>'','company_name'=>'','mobile'=>'']);
     }

     public function return_products()
     {
        return $this->hasMany(SaleReturnProduct::class,'sale_return_id','id'); 
     }
      /******************************************
      * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    protected $order = ['sr.id' => 'desc'];
     //custom search column property
     protected $_start_date; 
     protected $_end_date; 
     protected $_warehouse_id; 
 
     //methods to set custom search property value
     public function setStartDate($start_date){ $this->_start_date = $start_date; }
     public function setEndDate($end_date){ $this->_end_date = $end_date; }
     public function setWarehouseID($warehouse_id){ $this->_warehouse_id = $warehouse_id; }
 
 
     private function get_datatable_query()
     {
         //set column sorting index table column name wise (should match with frontend table header)
        $this->column_order = ['sr.id','sr.return_no','sr.memo_no', 'sr.customer_id', 's.salemen_id','s.district_id','s.upazila_id','s.route_id',
        's.area_id', null,null,null,null,null,null,null,null,null,'sr.total_deduction','sr.grand_total', 'sr.return_date',];
         
        $query = DB::table('sale_returns as sr')
        ->leftjoin('sales as s','sr.memo_no','=','s.memo_no')
        ->leftJoin('customers as c','sr.customer_id','=','c.id')
        ->leftJoin('salesmen as sm','s.salesmen_id','=','sm.id')
        ->join('locations as d', 's.district_id', '=', 'd.id')
        ->join('locations as u', 's.upazila_id', '=', 'u.id')
        ->join('locations as r', 's.route_id', '=', 'r.id')
        ->join('locations as a', 's.area_id', '=', 'a.id')
        ->select('sr.*','c.name as customer_name','c.shop_name','sm.name as salesman_name','d.name as district_name',
        'u.name as upazila_name','r.name as route_name','a.name as area_name',
        DB::raw('(SELECT SUM(srp.return_qty) FROM sale_return_products as srp
        WHERE srp.sale_return_id = sr.id GROUP BY srp.sale_return_id) as total_return_qty'),
        DB::raw('(SELECT COUNT(srp.id) FROM sale_return_products as srp
        WHERE srp.sale_return_id = sr.id GROUP BY srp.sale_return_id) as total_return_items'));
         //search query
         if (!empty($this->_start_date)) {
             $query->where('sr.return_date', '>=',$this->_start_date);
         }
         if (!empty($this->_end_date)) {
             $query->where('sr.return_date', '<=',$this->_end_date);
         }
 
         if (!empty($this->_warehouse_id)) {
            $query->where('sr.warehouse_id', $this->_warehouse_id);
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
         $query = DB::table('sale_returns');
         if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id);
        }
        return $query->get()->count();
     }
     /******************************************
      * * * End :: Custom Datatable Code * * *
     *******************************************/
}
