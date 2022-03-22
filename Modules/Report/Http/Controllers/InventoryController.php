<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\WarehouseProduct;

class InventoryController extends BaseController
{
    public function __construct(WarehouseProduct $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('inventory-report-access')){
            $this->setPageData('Inventory Report','Inventory Report','fas fa-file',[['name'=>'Inventory Report']]);
            $warehouses = DB::table('warehouses')->where('status',1)->pluck('name','id');
            return view('report::inventory',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('inventory-report-access')){

                if (!empty($request->batch_no)) {
                    $this->model->setBatchNo($request->batch_no);
                }
                if (!empty($request->product_id)) {
                    $this->model->setProductID($request->product_id);
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
                    $row[] = $value->name;
                    $row[] = $value->unit_name;
                    $row[] = number_format($value->qty,2,'.','');
                    $row[] = number_format($value->base_unit_price,2,'.','');
                    $row[] = number_format(($value->qty * $value->base_unit_price),2,'.','');
                    $row[] = $value->qty > 0 ? '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Available</span>' : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Out of Stock</span>';
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
