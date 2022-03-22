<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\MaterialIssueReport;

class MaterialIssueReportController extends BaseController
{
    public function __construct(MaterialIssueReport $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('material-issue-report-access')){
            $this->setPageData('Material Issue Report','Material Issue Report','fas fa-file',[['name' => 'Material Issue Report']]);
            $materials = Material::toBase()->select('id','material_name','material_code')->get();
            return view('report::material-issue-report',compact('materials'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('material-issue-report-access')){

                if (!empty($request->batch_no)) {
                    $this->model->setBatchNo($request->batch_no);
                }
                if (!empty($request->material_id)) {
                    $this->model->setMaterialID($request->material_id);
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


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->start_date;
                    $row[] = $value->batch_no;
                    $row[] = $value->material_name;
                    $row[] = $value->material_code;
                    $row[] = $value->category_name;
                    $row[] = MATERIAL_TYPE[$value->type];
                    $row[] = $value->unit_name;
                    $row[] = $value->used_qty;
                    $row[] = $value->damaged_qty ? $value->damaged_qty : 0;
                    $row[] = $value->used_qty + ($value->damaged_qty ? $value->damaged_qty : 0);
                    $row[] = $value->product_name;
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
