<?php

namespace Modules\Report\Entities;

use App\Models\Unit;
use App\Models\Category;
use App\Models\BaseModel;

class MaterialStockAlert extends BaseModel
{
    protected $table = "materials";
    protected $guarded = [];

     public function category()
     {
         return $this->belongsTo(Category::class);
     }
     public function unit()
     {
         return $this->belongsTo(Unit::class,'unit_id','id');
     }
    
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
        
        
        $query = self::with('category:id,name','unit:id,unit_name')
        ->where('status',1)->whereColumn('alert_qty','>','qty');

        //search query
        if (!empty($this->_name)) {
            $query->where('material_name', 'like', '%' . $this->_name . '%');
        }

        if (!empty($this->_category_id)) {
            $query->where('category_id', $this->_category_id);
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
        return self::toBase()->where('status',1)->whereColumn('alert_qty','>','qty')->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
