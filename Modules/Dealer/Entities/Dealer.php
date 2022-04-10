<?php

namespace Modules\Dealer\Entities;

use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Location\Entities\Upazila;
use Modules\Location\Entities\District;
use Modules\Account\Entities\Transaction;
use Modules\Dealer\Entities\DealerProduct;
use Modules\Account\Entities\ChartOfAccount;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Setting\Entities\Warehouse;

class Dealer extends Authenticatable
{
    protected $fillable = [ 'name', 'shop_name', 'mobile','username','password', 'email', 'avatar', 'warehouse_id',
     'district_id', 'upazila_id', 'address', 'status', 'created_by', 'modified_by'];

     protected $hidden = [
        'password',
        'remember_token',  
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
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
        ->withPivot('id','commission_rate','commission_percentage')
        ->withTimestamps();
    }

    public function hasManyProducts(){
        return $this->hasMany(DealerProduct::class,'dealer_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['id' => 'desc'];
    protected $column_order;
    protected $orderValue;
    protected $dirValue;
    protected $startVlaue;
    protected $lengthVlaue;

    public function setOrderValue($orderValue)
    {
        $this->orderValue = $orderValue;
    }
    public function setDirValue($dirValue)
    {
        $this->dirValue = $dirValue;
    }
    public function setStartValue($startVlaue)
    {
        $this->startVlaue = $startVlaue;
    }
    public function setLengthValue($lengthVlaue)
    {
        $this->lengthVlaue = $lengthVlaue;
    }

    //custom search column property
    protected $_name; 
    protected $_shop_name; 
    protected $_mobile; 
    protected $_email; 
    protected $_warehouse_id; 
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

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
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
                $this->column_order = ['id','id','avatar','name', 'shop_name', 'username','district_id','upazila_id','status',null,null];
            }else{
                $this->column_order = ['id','id','avatar','name', 'shop_name', 'username', 'warehouse_id','district_id','upazila_id','status',null,null];
            }
        }else{
            if(auth()->user()->warehouse_id)
            {
                $this->column_order = ['id','avatar','name', 'shop_name', 'username', 'district_id','upazila_id','status',null,null];
            }else{
                $this->column_order = ['id','avatar','name', 'shop_name', 'username', 'warehouse_id','district_id','upazila_id','status',null,null];
            }
        }
        
        
        
        $query = self::with('district:id,name','upazila:id,name','warehouse:id,name');
        
        if(auth()->user()->warehouse_id)
        {
            $query->where('warehouse_id',  auth()->user()->warehouse_id);
        }
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

        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id',  $this->_warehouse_id);
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
        $query = self::toBase();
        if(auth()->user()->warehouse_id)
        {
            $query->where('warehouse_id',  auth()->user()->warehouse_id);
        }
        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
    public function dealer_products_data($request)
    {
        $products = [];
        if($request->has('products'))
        {                        
            foreach ($request->products as $key => $value) {
                $products[$value['id']] = [
                    "commission_rate"       => $value['commission_rate'],
                    "commission_percentage" => $value['commission_percentage'],
                ];
            }
        }
        return $products;
    }

    public function dealer_coa(string $code,string $head_name,int $dealer_id)
    {
        return [
            'code'              => $code,
            'name'              => $head_name,
            'parent_name'       => 'Dealer Receivable',
            'level'             => 4,
            'type'              => 'A',
            'transaction'       => 1,
            'general_ledger'    => 2,
            'dealer_id'         => $dealer_id,
            'budget'            => 2,
            'depreciation'      => 2,
            'depreciation_rate' => '0',
            'status'            => 1,
            'created_by'        => auth()->user()->name
        ];
    }

    public function previous_balance_add($balance, int $dealer_coa_id, string $dealer_name, $warehouse_id) {
        if(!empty($balance) && !empty($dealer_coa_id) && !empty($dealer_name)){
            $transaction_id = generator(10);

            // dealer debit for previous balance
            $cosdr = array(
                'chart_of_account_id' => $dealer_coa_id,
                'warehouse_id'        => $warehouse_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'PR Balance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => "Dealer $dealer_name previous due $balance",
                'debit'               => $balance,
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
            $inventory = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', '10101')->value('id'),
                'warehouse_id'        => $warehouse_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'PR Balance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => 'Inventory credit for old sale to '.$dealer_name,
                'debit'               => 0,
                'credit'              => $balance,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 

            Transaction::insert([
                $cosdr,$inventory
            ]);
        }
    }

}
