<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\FinishGoodsReport;

class FinishGoodsInventoryController extends BaseController
{
    public function __construct(FinishGoodsReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('finish-goods-inventory-report-access')){
            $this->setPageData('Finish Goods Inventory Report','Finish Goods Inventory Report','fas fa-file',[['name' => 'Finish Goods Inventory Report']]);
            return view('report::finish-goods-inventory-report');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('finish-goods-inventory-report-access')){

                if (!empty($request->batch_no)) {
                    $this->model->setBatchNo($request->batch_no);
                }
                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
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

                    if($value->unit_operator == '*')
                    {
                        $unit_qty = $value->base_unit_qty / $value->unit_operation_value;
                    }else{
                        $unit_qty = $value->base_unit_qty * $value->unit_operation_value;
                    }
                    
                    $row = [];
                    $row[] = $no;
                    $row[] = date('d-M-Y',strtotime($value->end_date));
                    $row[] = $value->batch_no;
                    $row[] = $value->product_name;
                    $row[] = $value->product_code;
                    $row[] = $value->unit_name;
                    $row[] = $value->base_unit_name;
                    $row[] = number_format($value->unit_price,2,'.','');
                    $row[] = number_format($value->base_unit_price,2,'.','');
                    $row[] = number_format($value->per_unit_cost,2,'.','');
                    $row[] = number_format($value->base_unit_qty,4,'.','');
                    $row[] = number_format($unit_qty,4,'.','');
                    $row[] = number_format(($value->unit_price * $unit_qty),2,'.','');
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
