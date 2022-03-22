<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\SalesmanCommissionReport;
use Modules\SalesMen\Entities\Salesmen;

class SalesmanCommissionReportController extends BaseController
{
    public function __construct(SalesmanCommissionReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('sr-commission-report-access')){
            $this->setPageData('SR Commission Report','SR Commission Report','fas fa-file',[['name' => 'Report'],['name' => 'SR Commission Report']]);
            $salesmen = Salesmen::where('status',1)->get();
            return view('report::salesman-commission-report',compact('salesmen'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->warehouse_id)) {
                $this->model->setWarehouseID($request->warehouse_id);
            }
            if (!empty($request->salesmen_id)) {
                $this->model->setSalesmanID($request->salesmen_id);
            }
            if (!empty($request->start_date)) {
                $this->model->setStartDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setEndDate($request->end_date);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $total_deducted_commission = (($value->total_return_deducted_commission ? $value->total_return_deducted_commission : 0) + ($value->total_damage_deducted_commission ? $value->total_damage_deducted_commission : 0));
                $earned_commission = ($value->total_commission ? $value->total_commission : 0) - $total_deducted_commission;
                $row = [];
                $row[] = $no;
                $row[] = $value->name.' - '.$value->phone;
                $row[] = number_format($value->total_commission,2);
                $row[] = number_format($total_deducted_commission,2);
                $row[] = number_format($earned_commission,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
