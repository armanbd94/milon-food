<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Report\Entities\ReceivedCoupon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class ReceivedCouponController extends BaseController
{
    public function __construct(ReceivedCoupon $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('coupon-received-report-access')){
            $this->setPageData('Coupon Received Report','Coupon Received Report','fas fa-qrcode',[['name'=>'Coupon Received Report']]);
            $data = [
                'districts'   => DB::table('locations')->where([['status', 1],['parent_id',0]])->pluck('name','id'),
                'warehouses'  => DB::table('warehouses')->where('status',1)->pluck('name','id')
            ];
            return view('report::received-coupons',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('coupon-received-report-access')){

                if (!empty($request->batch_no)) {
                    $this->model->setBatchNo($request->batch_no);
                }
                if (!empty($request->product_id)) {
                    $this->model->setProductID($request->product_id);
                }
                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->warehouse_id)) {
                    $this->model->setWarehouseID($request->warehouse_id);
                }
                if (!empty($request->salesmen_id)) {
                    $this->model->setSalesmenID($request->salesmen_id);
                }
                if (!empty($request->customer_id)) {
                    $this->model->setCustomerID($request->customer_id);
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
                    $row[] = $value->batch_no;
                    $row[] = $value->coupon_code;
                    $row[] = $value->status == 1 ? 'Verified' : 'Not Verified';
                    $row[] = $value->product_name;
                    $row[] = $value->received_from;
                    $row[] = $value->received_by;
                    $row[] = $value->district_name;
                    $row[] = $value->upazila_name;
                    $row[] = $value->route_name;
                    $row[] = $value->area_name;
                    $row[] = date('d-M-Y h:i A',strtotime($value->created_at));
                    $row[] = number_format($value->coupon_price,2,'.','');
                   $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
