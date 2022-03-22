<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class SalesmanCommissionReport extends BaseModel
{
    protected $table = 'salesmen';

    protected $guarded = [];
    

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['sm.id' => 'asc'];
    protected $_warehouse_id; 
    protected $_salesmen_id; 
    protected $_start_date; 
    protected $_end_date; 

    //methods to set custom search property value
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }
    public function setSalesmanID($salesmen_id)
    {
        $this->_salesmen_id = $salesmen_id;
    }
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = [null,'sm.name', null, null];
        
        
        $query = DB::table('salesmen as sm')
                ->selectRaw("SUM(s.total_commission) as total_commission, SUM(sr.deducted_sr_commission) as total_return_deducted_commission,SUM(d.deducted_sr_commission) as total_damage_deducted_commission,sm.name, sm.phone")
                ->leftjoin('sales as s','sm.id','=','s.salesmen_id')
                ->leftjoin('sale_returns as sr','s.memo_no','=','sr.memo_no')
                ->leftjoin('damages as d','s.memo_no','=','d.memo_no')
                ->leftjoin('warehouses as w','s.warehouse_id','=','w.id')
                ->groupBy('s.salesmen_id');

        if (!empty($this->_salesmen_id)) {
            $query->where('sm.id', $this->_salesmen_id);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('s.warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereDate('s.sale_date', '>=',$this->_start_date)
            ->whereDate('s.sale_date', '<=',$this->_end_date);
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
        return DB::table('salesmen')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
