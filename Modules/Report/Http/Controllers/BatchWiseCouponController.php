<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\BatchWiseCouponReport;

class BatchWiseCouponController extends BaseController
{
    public function __construct(BatchWiseCouponReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('batch-wise-coupon-report-access')){
            $this->setPageData('Batch Wise Coupon Report','Batch Wise Coupon Report','fas fa-file',[['name'=>'Batch Wise Coupon Report']]);
            return view('report::batch-wise-coupon-report');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('batch-wise-coupon-report-access')){

                if (!empty($request->batch_no)) {
                    $this->model->setBatchNo($request->batch_no);
                }
                if (!empty($request->product_id)) {
                    $this->model->setProductID($request->product_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->warehouse_name;
                    $row[] = $value->batch_no;
                    $row[] = $value->product_name;
                    $row[] = $value->total_coupon;
                    $row[] = $value->total_used_coupons;
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
