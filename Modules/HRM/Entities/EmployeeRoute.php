<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;

class EmployeeRoute extends BaseModel
{
    protected $fillable = ['name','short_name','status', 'deletable', 'created_by', 'modified_by'];


    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_name; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('route-bulk-delete')){
            $this->column_order = [null,'id','name','short_name','status',null];
        }else{
            $this->column_order = ['id','name','short_name','status',null];
        }
        
        $query = self::toBase();

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
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

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_EMPLOYEE_ROUTES    = '_employee_routes';
    protected const ACTIVE_EMPLOYEE_ROUTES    = '_active_employee_routes';

    public static function allEmployee_Routes(){
        return Cache::rememberForever(self::ALL_EMPLOYEE_ROUTES, function () {
            return self::toBase()->get();
        });
    }
    public static function activeEmployee_Routes(){
        return Cache::rememberForever(self::ACTIVE_EMPLOYEE_ROUTES, function () {
            return self::toBase()->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_EMPLOYEE_ROUTES);
        Cache::forget(self::ACTIVE_EMPLOYEE_ROUTES);
    }


    public static function boot(){
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function() {
            self::flushCache();
        });

        static::deleted(function() {
            self::flushCache();
        });
    }
    /***********************************
    * * *  Begin :: Cache Data * * *
    ************************************/
}
