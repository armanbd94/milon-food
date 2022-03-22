<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class MaterialIssueReport extends BaseModel
{
    protected $table = 'production_product_materials';
    protected $fillable = ['production_product_id', 'material_id', 'unit_id','qty','cost','total', 'used_qty', 'damaged_qty', 'odd_qty'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_batch_no; 
    protected $_start_date; 
    protected $_end_date; 
    protected $_material_id; 
    protected $_product_id; 

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
    public function setMaterialID($material_id)
    {
        $this->_material_id = $material_id;
    }
    public function setProductID($product_id)
    {
        $this->_product_id = $product_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = [];
        
        
        $query = DB::table('production_product_materials as ppm')
        ->leftJoin('production_products as pp','ppm.production_product_id','pp.id')
        ->leftJoin('productions as pro','pp.production_id','pro.id')
        ->leftJoin('products as p','pp.product_id','p.id')
        ->leftJoin('materials as m','ppm.material_id','m.id')
        ->leftJoin('categories as c','m.category_id','c.id')
        ->leftJoin('units as u','ppm.unit_id','u.id')
        ->selectRaw('ppm.*,pro.batch_no,pro.start_date,p.name as product_name,m.material_name,m.material_code,m.type,u.unit_name,c.name as category_name');
        //search query
        if (!empty($this->_batch_no)) {
            $query->where('pro.batch_no', $this->_batch_no);
        }

        if (!empty($this->_start_date)) {
            $query->where('pro.start_date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('pro.start_date', '<=',$this->_end_date);
        }
        if (!empty($this->_material_id)) {
            $query->where('ppm.material_id', $this->_material_id);
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
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
