<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class ProductStockAlert extends BaseModel
{
    protected $table = "warehouse_product";
    protected $guarded = [];


    
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property

    protected $_name; 
    protected $_category_id; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setCategoryID($category_id)
    {
        $this->_category_id = $category_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['id', 'material_name', 'material_code','category_id', 'unit_id', 'qty', 'alert_qty'];
        
        
        $query = DB::table('warehouse_product as wp')
        ->join('products as p','wp.product_id','=','p.id')
        ->join('categories as c','p.category_id','=','c.id')
        ->join('units as u','p.base_unit_id','=','u.id')
        ->selectRaw('wp.*,p.name,p.code,p.alert_quantity,c.name as category_name,u.unit_name')
        ->groupBy('wp.product_id')
        ->whereColumn('p.alert_quantity','>','wp.qty');

        //search query
        if (!empty($this->_name)) {
            $query->where('p.name', 'like', '%' . $this->_name . '%');
        }

        if (!empty($this->_category_id)) {
            $query->where('p.category_id', $this->_category_id);
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
        return DB::table('warehouse_product as wp')
        ->join('products as p','wp.product_id','=','p.id')
        ->join('categories as c','p.category_id','=','c.id')
        ->join('units as u','p.base_unit_id','=','u.id')
        ->selectRaw('wp.*,p.name,c.name as category_name,u.unit_name')
        ->groupBy('wp.product_id')
        ->whereColumn('p.alert_quantity','>','wp.qty')->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
