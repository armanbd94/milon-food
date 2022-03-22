<?php
namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;

class DailyClosing extends BaseModel
{
    protected $fillable = [ 'warehouse_id','last_day_closing', 'cash_in', 'cash_out', 'balance','transfer',
    'closing_amount', 'adjustment', 'date','thousands', 'five_hundred', 'two_hundred','hundred', 'fifty', 'twenty', 'ten', 'five', 'two', 'one', 'created_by', 'modified_by'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withDefault(['name'=>'']);
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property

    protected $start_date; 
    protected $end_date; 
    protected $_warehouse_id; 

    //methods to set custom search property value
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }
    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if(auth()->user()->warehouse_id)
        {
            $this->column_order = ['id','date', 'cash_in','cash_out','closing_amount'];
        }else{
            $this->column_order = ['id','warehouse_id','date', 'cash_in','cash_out','closing_amount'];
        }
        
        
        $query = self::with('warehouse');
        if(auth()->user()->warehouse_id)
        {
            $query->where('warehouse_id',  auth()->user()->warehouse_id);
        }
        //search query
        if (!empty($this->start_date)) {
            $query->where('date', '>=',$this->start_date);
        }
        if (!empty($this->end_date)) {
            $query->where('date', '<=',$this->end_date);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id',  $this->_warehouse_id);
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
        $query = self::toBase();
        if(auth()->user()->warehouse_id)
        {
            $query->where('warehouse_id',  auth()->user()->warehouse_id);
        }

        return $query->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
