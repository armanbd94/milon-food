<?php

namespace Modules\ASM\Entities;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Modules\Location\Entities\District;
use Modules\Setting\Entities\Warehouse;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ASM extends Authenticatable
{
    protected $table = 'asms';
    protected $fillable = [
        'name','username','email','phone','avatar','password','district_id','address','nid_no','monthly_target_value','status','created_by','modified_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',  
    ];

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class,'asm_id','id')->withDefault(['name'=>'-']);
    }
    public function district()
    {
        return $this->belongsTo(District::class,'district_id','id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function district_id_wise_asm_list(int $id)
    {
        return self::where('district_id',$id)->pluck('name','id');
    }

    public function module_asm(){
        return $this->belongsToMany(Module::class,ASMModule::class,'asm_id','module_id','id','id')->withTimestamps();
    }

    public function permission_asm(){
        return $this->belongsToMany(Permission::class,ASMPermission::class,'asm_id','permission_id','id','id')->withTimestamps();
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

    protected $_name;
    // protected $_username;
    protected $_phone;
    protected $_email;
    protected $_district_id;
    protected $_status;

    public function setName($name)
    {
        $this->_name = $name;
    }
    // public function setUsername($username)
    // {
    //     $this->_username = $username;
    // }
    public function setPhone($phone)
    {
        $this->_phone = $phone;
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
    public function setStatus($status)
    {
        $this->_status = $status;
    }


    private function get_datatable_query()
    { 
        if (permission('user-bulk-delete')){
            $this->column_order = [null,'id','id','name','warehouse_id','district_id','phone','email','status', null];
        }else{
            $this->column_order = ['id','id','name','warehouse_id','district_id','phone','email','status', null];
        }
        

        $query = self::with('warehouse','district');

        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        // if (!empty($this->_username)) {
        //     $query->where('username', 'like', '%' . $this->_username . '%');
        // }
        if (!empty($this->_phone)) {
            $query->where('phone', 'like', '%' . $this->_phone . '%');
        }
        if (!empty($this->_email)) {
            $query->where('email', 'like', '%' . $this->_email . '%');
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_district_id)) {
            $query->where('district_id', $this->_district_id);
        }
        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
        }

        if (isset($this->orderValue) && isset($this->dirValue)) {
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue);
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
