<?php

namespace App\Models;


use App\Models\Role;
use TypiCMS\NestableTrait;
use Illuminate\Support\Facades\Hash;
use Modules\Setting\Entities\Warehouse;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable,NestableTrait;


    protected $fillable = [
        'warehouse_id', 'parent_id','role_id','name','username','email','phone','avatar','gender','password','status','deletable','created_by','modified_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',  
    ];

    
    
    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /***************************************
     * * * Begin :: Model Relationship * * *
    ****************************************/
    public function setPasswordAttribute($value){
        $this->attributes['password'] = Hash::make($value);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    } 
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withDefault(['name'=>'']);
    } 
    public function parent(){
        return $this->belongsTo(User::class,'parent_id','id')->withDefault(['name'=>'']);
    }

    public static function user_list(){
        $users = self::orderBy('id','asc')
        ->where('id','!=',1)
        ->get()
        ->nest()
        ->setIndent('-- ')
        ->listsFlattened('name');

        return $users;
    }
     /***************************************
     * * * End :: Model Relationship * * *
    ****************************************/

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
    protected $_username;
    protected $_phone;
    protected $_email;
    protected $_role_id;
    protected $_parent_id;
    protected $_status;

    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setUsername($username)
    {
        $this->_username = $username;
    }
    public function setPhone($phone)
    {
        $this->_phone = $phone;
    }
    public function setEmail($email)
    {
        $this->_email = $email;
    }
    public function setRoleID($role_id)
    {
        $this->_role_id = $role_id;
    }
    public function setParentID($parent_id)
    {
        $this->_parent_id = $parent_id;
    }
    public function setStatus($status)
    {
        $this->_status = $status;
    }
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    private function get_datatable_query()
    { 
        if (permission('user-bulk-delete')){
            $this->column_order = [null,'id','id','name','username','role_id','warehouse_id','parent_id','status','created_by','created_at',null];
        }else{
            $this->column_order = ['id','id','name','username','role_id','warehouse_id','parent_id','status','created_by','created_at',null];
        }
        

        $query = self::with('role:id,role_name','parent:id,name','warehouse:id,name')->where('id','!=',1);

        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_username)) {
            $query->where('username', 'like', '%' . $this->_username . '%');
        }
        if (!empty($this->_phone)) {
            $query->where('phone', 'like', '%' . $this->_phone . '%');
        }
        if (!empty($this->_email)) {
            $query->where('email', 'like', '%' . $this->_email . '%');
        }
        if (!empty($this->_role_id)) {
            $query->where('role_id', $this->_role_id );
        }
        if (!empty($this->_parent_id)) {
            $query->where('parent_id', $this->_parent_id );
        }
        if (!empty($this->_status)) {
            $query->where('status', $this->_status );
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id );
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
        return self::toBase()->where('id','!=',1)->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    public static function user_html_list()
    {
        $users = '';
        $users .= (new self)->user_html_data(0);
        return $users;
    }

    private function user_html_data($parent_id=0,$level = 0)
    {
        $user = '';
        if($parent_id == 0){
            $users = self::with('role')->where([['parent_id', 0],['id','!=',1]])->orderBy('id','asc')->get(); //get user list whose parent id is 0
        }else{
            $users = self::with('role')->where([['parent_id',$parent_id],['id','!=',1]])->orderBy('id','asc')->get(); //get user list whose parent id is the given id
        }
        if(!$users->isEmpty()){
            foreach ($users as $value) {
                if($value->parent_id == 0)
                {
                    $user .= "<option value='$value->id'>".$value->name." (".$value->role->role_name.")";
                    $user .= $this->user_html_data($value->id,$level+1);
                    $user .= "</option>";
                }else{
                    $user .= "<option value='$value->id'>".str_repeat("&#9866;", $level)." ".$value->name." (".$value->role->role_name.")";
                    $user .= $this->user_html_data($value->id,$level+1);
                    $user .= "</option>";
                }
            }
        }
        return $user;
    }

}
