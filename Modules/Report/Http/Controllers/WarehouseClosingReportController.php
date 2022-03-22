<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\WarehouseClosingReport;

class WarehouseClosingReportController extends BaseController
{
    public function __construct(WarehouseClosingReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('warehouse-closing-report-access')){
            $this->setPageData('Warehouse Closing Report','Warehouse Closing Report','fas fa-file-signature',[['name' => 'Report'],['name' => 'Warehouse Closing Report']]);
            $warehouses = DB::table('warehouses')->where('status',1)->pluck('name','id');
            return view('report::warehouse-closing-report',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
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
                $row = [];
                $row[] = $no;
                $row[] = $value->warehouse_name;
                $row[] = date('d-M-Y',strtotime($value->date));
                $row[] = number_format($value->cash_in,2, '.', '') ;
                $row[] = number_format($value->cash_out,2, '.', '');
                $row[] = number_format(($value->balance),2, '.', '');
                $row[] = number_format(($value->transfer),2, '.', '');
                $row[] = number_format(($value->closing_amount),2, '.', '');
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
