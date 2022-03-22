<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\CustomerReceipt;

class CustomerReceiptController extends BaseController
{
    public function __construct(CustomerReceipt $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('customer-receipt-list-access')){
            $this->setPageData('Customer Receipt','Customer Receipt','fas fa-file',[['name' => 'Customer Receipt']]);
            $data = [
                'districts'   => DB::table('locations')->where([['status', 1],['parent_id',0]])->pluck('name','id'),
                'warehouses'  => DB::table('warehouses')->where('status',1)->pluck('name','id')
            ];
            return view('report::customer-receipt-list',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->start_date)) {
                $this->model->setFromDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setToDate($request->end_date);
            }
            if (!empty($request->customer_id)) {
                $this->model->setCustomerID($request->customer_id);
            }
            if (!empty($request->warehouse_id)) {
                $this->model->setWarehouseID($request->warehouse_id);
            }
            if (!empty($request->district_id)) {
                $this->model->setDistrictID($request->district_id);
            }
            
            if (!empty($request->upazila_id)) {
                $this->model->setUpazilaID($request->upazila_id);
            }
            if (!empty($request->route_id)) {
                $this->model->setRouteID($request->route_id);
            }

            if (!empty($request->area_id)) {
                $this->model->setAreaID($request->area_id);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = date('d-m-Y',strtotime($value->voucher_date));
                $row[] = $value->shop_name.' - '.$value->customer_name;
                $row[] = $value->district_name;
                $row[] = $value->upazila_name;
                $row[] = $value->route_name;
                $row[] = $value->area_name;
                $row[] = $value->description;
                $row[] = number_format($value->credit,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
