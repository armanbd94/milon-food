<?php

namespace Modules\Production\Entities;

use App\Models\BaseModel;
use Modules\Setting\Entities\Warehouse;
use Modules\Production\Entities\ProductionProduct;

class Production extends BaseModel
{
    protected $fillable = ['batch_no', 'warehouse_id', 'start_date', 'end_date', 'item', 'status', 'production_status','created_by', 'modified_by'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
    public function products()
    {
        return $this->hasMany(ProductionProduct::class,'production_id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_batch_no; 
    protected $_warehouse_id; 
    protected $_start_date;  
    protected $_end_date;  
    protected $_status; 
    protected $_production_status; 
    protected $_transfer_status; 

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

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    public function setProductionStatus($production_status)
    {
        $this->_production_status = $production_status;
    }

    public function setTransferStatus($transfer_status)
    {
        $this->_transfer_status = $transfer_status;
    }


    private function get_datatable_query()
    {

        $this->column_order = ['id', 'batch_no', 'warehouse_id', 'start_date', 'end_date', 'item','status', 'production_status', null];
        
        
        $query = self::with('warehouse:id,name');

        //search query
        if (!empty($this->_batch_no)) {
            $query->where('batch_no', $this->_batch_no);
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereDate('start_date','>=', $this->_start_date)->whereDate('start_date','<=', $this->_end_date);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
        }
        if (!empty($this->_production_status)) {
            $query->where('production_status', $this->_production_status);
        }
        if (!empty($this->_transfer_status)) {
            $query->where('transfer_status', $this->_transfer_status);
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
