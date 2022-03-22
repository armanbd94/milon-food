<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\TodaySalesReport;

class TodaySalesReportController extends BaseController
{

    public function __construct(TodaySalesReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('today-sales-report-access')){
            $this->setPageData('Today\'s Sales Report','Today\' Sales Report','fas fa-file',[['name' => 'Today\' Sales Report']]);
            $data = [
                'districts'   => DB::table('locations')->where([['status', 1],['parent_id',0]])->pluck('name','id'),
                'warehouses'  => DB::table('warehouses')->where('status',1)->pluck('name','id')
            ];
            return view('report::today-sales-report',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->memo_no)) {
                $this->model->setMemoNo($request->memo_no);
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
            if (!empty($request->area_id)) {
                $this->model->setAreaID($request->area_id);
            }
            if (!empty($request->upazila_id)) {
                $this->model->setUpazilaID($request->upazila_id);
            }
            if (!empty($request->route_id)) {
                $this->model->setRouteID($request->route_id);
            }
            if (!empty($request->payment_status)) {
                $this->model->setPaymentStatus($request->payment_status);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties

            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = $value->memo_no;
                $row[] = $value->salesman_name;
                $row[] = $value->upazila_name;
                $row[] = $value->route_name;
                $row[] = $value->area_name;
                $row[] = $value->shop_name.' - '.$value->name;
                $row[] = number_format($value->grand_total,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
