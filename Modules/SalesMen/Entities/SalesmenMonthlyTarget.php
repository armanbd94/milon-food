<?php

namespace Modules\SalesMen\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\SalesMen\Entities\Salesmen;

class SalesmenMonthlyTarget extends BaseModel
{
    protected $table = 'salesmen_monthly_targets';
    protected $fillable = [
        'mtcode','salesmen_id','target_value','achieved_value','commission_rate','commission_earned','start_date','end_date','closing_date','created_by','modified_by'
    ];

    public function salesmen()
    {
        return $this->belongsTo(Salesmen::class,'salesmen_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['id' => 'desc'];

    //custom search column property
    protected $_warehouse_id; 
    protected $_salesmen_id; 
    protected $_start_date; 
    protected $_end_date; 

    //methods to set custom search property value
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }
    public function setSalesmenID($salesmen_id)
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

        if(permission('sr-monthly-target-bulk-delete'))
        {
            $this->column_order = ['smt.id','smt.id','smt.start_date','smt.salesmen_id', 's.warehouse_id', 'smt.target_value','smt.achieved_value','smt.commission_rate','smt.commission_earned','smt.closing_date','smt.created_by',null];
        }else{
            $this->column_order = ['smt.id','smt.start_date','smt.salesmen_id', 's.warehouse_id', 'smt.target_value','smt.achieved_value','smt.commission_rate','smt.commission_earned','smt.closing_date','smt.created_by',null];
        }
        
        $query = DB::table($this->table.' as smt')
        ->join('salesmen as s','smt.salesmen_id','=','s.id')
        ->join('warehouses as w','s.warehouse_id','=','w.id')
        ->selectRaw('smt.*,s.name as salesman_name,s.phone,w.name as warehouse_name')
        ->where('smt.start_date','>=',$this->_start_date)
        ->where('smt.end_date','<=',$this->_end_date);
        
        if(auth()->user()->warehouse_id)
        {
            $query->where('s.warehouse_id',  auth()->user()->warehouse_id);
        }
        //search query
        if (!empty($this->_warehouse_id)) {
            $query->where('s.warehouse_id',  $this->_warehouse_id);
        }
        if (!empty($this->_salesmen_id)) {
            $query->where('smt.salesmen_id',  $this->_salesmen_id);
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
        $query = DB::table($this->table.' as smt')
        ->join('salesmen as s','smt.salesmen_id','=','s.id')
        ->join('warehouses as w','s.warehouse_id','=','w.id')
        ->selectRaw('smt.*,s.name as salesman_name,s.phone,w.name as warehouse_name')
        ->where('smt.start_date','>=',$this->_start_date)
        ->where('smt.end_date','<=',$this->_end_date);
        
        if(auth()->user()->warehouse_id)
        {
            $query->where('s.warehouse_id',  auth()->user()->warehouse_id);
        }
        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
    
}
