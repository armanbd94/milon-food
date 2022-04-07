<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Warehouse;
use Modules\Account\Entities\ChartOfAccount;


class CustomerAdvance extends BaseModel
{
    protected $table = 'transactions';
    protected $order = ['transactions.id' => 'desc'];
    protected $fillable = ['chart_of_account_id', 'warehouse_id', 'voucher_no', 'voucher_type', 'voucher_date',
     'description', 'debit', 'credit', 'is_opening', 'posted', 'approve', 'created_by', 'modified_by'];
    private const TYPE = 'Advance'; 

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'chart_of_account_id','id');
    }

    public function customer()
    {
        return $this->hasOneThrough(Customer::class,ChartOfAccount::class,'customer_id','chart_of_account_id','id','id');
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_customer_id; 
    protected $_district_id; 
    protected $_upazila_id; 
    protected $_area_id; 
    protected $_warehouse_id;
    protected $_type; 
    protected $_start_date; 
    protected $_end_date; 

    //methods to set custom search property value
    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }
    public function setDistrictID($district_id)
    {
        $this->_district_id = $district_id;
    }
    public function setUpazilaID($upazila_id)
    {
        $this->_upazila_id = $upazila_id;
    }
    public function setAreaID($area_id)
    {
        $this->_area_id = $area_id;
    }
    public function setType($type)
    {
        $this->_type = $type;
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

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['transactions.id','transactions.voucher_date','c.name', 'c.warehouse_id','c.district_id','c.upazila_id','c.area_id',null,null,null,null,null];
        
        
        $query = self::select('transactions.*','coa.id as coa_id','coa.code','c.id as customer_id','c.name as customer_name',
        'c.shop_name','c.mobile','d.name as district_name','u.name as upazila_name','a.name as area_name','w.name as warehouse_name')
        ->join('warehouses as w','transactions.warehouse_id','=','w.id')
        ->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
        ->join('customers as c','coa.customer_id','c.id')
        ->join('locations as d', 'c.district_id', '=', 'd.id')
        ->join('locations as u', 'c.upazila_id', '=', 'u.id')
        ->join('locations as a', 'c.area_id', '=', 'a.id')
        ->where([
            'transactions.voucher_type'=>self::TYPE,
            'transactions.approve'=>1,
        ]);
        if(auth()->user()->warehouse_id)
        {
            $query->where('transactions.warehouse_id',  auth()->user()->warehouse_id);
        }
        //search query
        if (!empty($this->customer_id)) {
            $query->where('c.id', $this->customer_id);
        }
        if (!empty($this->_district_id)) {
            $query->where('c.district_id', $this->_district_id);
        }
        if (!empty($this->_upazila_id)) {
            $query->where('c.upazila_id', $this->_upazila_id);
        }
        if (!empty($this->_area_id)) {
            $query->where('c.area_id', $this->_area_id);
        }
        if (!empty($this->_type) && $this->_type == 'debit') {
            $query->where('transactions.debit', '!=',0);
        }
        if (!empty($this->_type) && $this->_type == 'credit') {
            $query->where('transactions.credit', '!=',0);
        }
        if (!empty($this->_start_date)) {
            $query->where('transactions.voucher_date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('transactions.voucher_date', '<=',$this->_end_date);
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
        $query = self::select('transactions.*','coa.id as coa_id','coa.code','c.id as customer_id','c.name','c.shop_name','c.mobile')
        ->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
        ->join('customers as c','coa.customer_id','c.id')
        ->where([
            'transactions.voucher_type' => self::TYPE,
            'transactions.approve'      => 1,
        ]);
        if(auth()->user()->warehouse_id)
        {
            $query->where('transactions.warehouse_id',  auth()->user()->warehouse_id);
        }
        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
