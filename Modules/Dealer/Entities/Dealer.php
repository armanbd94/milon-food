<?php

namespace Modules\Dealer\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Location\Entities\Upazila;
use Modules\Location\Entities\District;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;


class Dealer extends BaseModel
{
    protected $fillable = [ 'name', 'shop_name', 'mobile', 'email', 'avatar', 
     'district_id', 'upazila_id', 'address', 'status', 'created_by', 'modified_by'];


    public function district()
    {
        return $this->belongsTo(District::class,'district_id','id');
    }
    public function upazila()
    {
        return $this->belongsTo(Upazila::class,'upazila_id','id');
    }

    public function coa(){
        return $this->hasOne(ChartOfAccount::class,'dealer_id','id');
    }
    
    public function previous_balance()
    {
        return $this->hasOneThrough(Transaction::class,ChartOfAccount::class,'dealer_id','chart_of_account_id','id','id')
        ->where('voucher_type','PR Balance')->withDefault(['debit' => '']);
    }

    public function balance(int $id)
    {
        $data = DB::table('dealers as d')
            ->selectRaw('d.id,b.id as coaid,b.code,((select ifnull(sum(debit),0) from transactions where chart_of_account_id= b.id AND approve = 1)-(select ifnull(sum(credit),0) from transactions where chart_of_account_id= b.id AND approve = 1)) as balance')
            ->leftjoin('chart_of_accounts as b', 'd.id', '=', 'b.dealer_id')
            ->where('d.id',$id)->first();
        $balance = 0;
        if($data)
        {
            $balance = $data->balance ? $data->balance : 0;
        }
        return $balance;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class,'dealer_products','dealer_id','product_id','id','id')
        ->withPivot('id','commission_amount')
        ->withTimestamps();
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_name; 
    protected $_shop_name; 
    protected $_mobile; 
    protected $_email; 
    protected $_district_id; 
    protected $_upazila_id; 
    protected $_status; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setShopName($shop_name)
    {
        $this->_shop_name = $shop_name;
    }
    public function setMobile($mobile)
    {
        $this->_mobile = $mobile;
    }
    public function setEmail($email)
    {
        $this->_email = $email;
    }

    public function setDistrictID($district_id)
    {
        $this->_district_id = $district_id;
    }
    public function setUpazilaID($upazila_id)
    {
        $this->_upazila_id = $upazila_id;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if(permission('dealer-bulk-delete'))
        {
            if(auth()->user()->warehouse_id)
            {
                $this->column_order = ['id','id','avatar','name', 'shop_name', 'district_id','upazila_id','status',null,null];
            }else{
                $this->column_order = ['id','id','avatar','name', 'shop_name', 'warehouse_id','district_id','upazila_id','status',null,null];
            }
        }else{
            if(auth()->user()->warehouse_id)
            {
                $this->column_order = ['id','avatar','name', 'shop_name', 'district_id','upazila_id','status',null,null];
            }else{
                $this->column_order = ['id','avatar','name', 'shop_name', 'warehouse_id','district_id','upazila_id','status',null,null];
            }
        }
        
        
        
        $query = self::with('district:id,name','upazila:id,name');

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_shop_name)) {
            $query->where('shop_name', 'like', '%' . $this->_shop_name . '%');
        }
        if (!empty($this->_mobile)) {
            $query->where('mobile', 'like', '%' . $this->_mobile . '%');
        }
        if (!empty($this->_email)) {
            $query->where('email', 'like', '%' . $this->_email . '%');
        }

        if (!empty($this->_district_id)) {
            $query->where('district_id',  $this->_district_id);
        }

        if (!empty($this->_upazila_id)) {
            $query->where('upazila_id',  $this->_upazila_id);
        }

        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
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
