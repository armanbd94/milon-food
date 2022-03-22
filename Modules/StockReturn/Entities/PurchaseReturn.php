<?php

namespace Modules\StockReturn\Entities;

use App\Models\BaseModel;
use Modules\Purchase\Entities\Purchase;
use Modules\Setting\Entities\Warehouse;
use Modules\Supplier\Entities\Supplier;
use Modules\StockReturn\Entities\PurchaseReturnMaterial;

class PurchaseReturn extends BaseModel
{
    protected $fillable = ['return_no', 'memo_no',  'warehouse_id','supplier_id', 'total_price', 'total_deduction', 'tax_rate',
    'total_tax', 'grand_total', 'reason', 'date', 'return_date', 'created_by', 'modified_by'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class,'memo_no','memo_no');
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id','id')->withDefault(['name'=>'','company_name'=>'','mobile'=>'']);
    }
 
    public function return_materials()
    {
        return $this->hasMany(PurchaseReturnMaterial::class,'purchase_return_id','id'); 
    }

    /******************************************
    * * * Begin :: Custom Datatable Code * * *
    *******************************************/
     //custom search column property
     protected $_return_no; 
     protected $_memo_no; 
     protected $_from_date; 
     protected $_to_date; 
     protected $_supplier_id; 
 
     //methods to set custom search property value
     public function setReturnNo($return_no)
     {
         $this->_return_no = $return_no;
     }
     public function setInvoiceNo($memo_no)
     {
         $this->_memo_no = $memo_no;
     }
 
     public function setFromDate($from_date)
     {
         $this->_from_date = $from_date;
     }
     public function setToDate($to_date)
     {
         $this->_to_date = $to_date;
     }
     public function setSupplierID($supplier_id)
     {
         $this->_supplier_id = $supplier_id;
     }
 
 
     private function get_datatable_query()
     {
         //set column sorting index table column name wise (should match with frontend table header)
         if(permission('purchase-return-bulk-delete')){
             $this->column_order = ['id','id','return_no','memo_no', null, 'return_date', 'grand_total', null];
         
         }else{
             $this->column_order = ['id','return_no','memo_no', null, 'return_date', 'grand_total', null];
         }
         
         $query = self::with('supplier');
 
         //search query
         if (!empty($this->_return_no)) {
             $query->where('return_no', 'like', '%' . $this->_return_no . '%');
         }
         if (!empty($this->_memo_no)) {
             $query->where('memo_no', 'like', '%' . $this->_memo_no . '%');
         }
 
         if (!empty($this->_from_date)) {
             $query->where('return_date', '>=',$this->_from_date);
         }
         if (!empty($this->_to_date)) {
             $query->where('return_date', '<=',$this->_to_date);
         }

         if (!empty($this->_supplier_id)) {
             $query->where('supplier_id', $this->_supplier_id);
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
