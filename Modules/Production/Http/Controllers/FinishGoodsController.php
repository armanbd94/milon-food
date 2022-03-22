<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Production\Entities\ProductionProduct;

class FinishGoodsController extends BaseController
{
    public function __construct(ProductionProduct $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('finish-goods-access')){
            $this->setPageData('Production Finish Goods','Production Finish Goods','fas fa-industry',[['name' => 'Production Finish Goods']]);
            $warehouses = DB::table('warehouses')->where('status',1)->pluck('name','id');
            return view('production::finish-goods.index',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('finish-goods-access')){

                if (!empty($request->batch_no)) {
                    $this->model->setBatchNo($request->batch_no);
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

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->batch_no;
                    $row[] = $value->warehouse_name;
                    $row[] = $value->product_name.' ('.$value->product_code.')';
                    $row[] = date('d-M-Y',strtotime($value->mfg_date));
                    $row[] = date('j-F-Y',strtotime($value->exp_date));
                    $row[] = $value->base_unit_name.' ('.$value->base_unit_code.')';
                    $row[] = $value->base_unit_qty;
                    $row[] = number_format($value->per_unit_cost,2,'.','');
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
