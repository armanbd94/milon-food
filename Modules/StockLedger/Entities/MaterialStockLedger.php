<?php

namespace Modules\StockLedger\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class MaterialStockLedger extends BaseModel
{
    protected $table = 'materials';
    protected $guarded = [];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_material_id; 


    //methods to set custom search property value
    public function setMaterialID($material_id)
    {
        $this->_material_id = $material_id;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = [];
        
        $query = self::with('unit:id,unit_name,unit_code','purchase_unit:id,unit_name,unit_code','category:id,name');

        //search query
        if (!empty($this->_material_id)) {
            $query->where('id', $this->_material_id);
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
