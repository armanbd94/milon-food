<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\ProductStockAlert;

class ProductStockAlertController extends BaseController
{
    public function __construct(ProductStockAlert $model)
    {
        $this->model = $model;
    }
    public function index()
    {
        if(permission('product-stock-alert-report-access')){
            $this->setPageData('Product Stock Alert Report','Product Stock Alert Report','fas fa-boxes',[['name' => 'Product Stock Alert Report']]);
            $categories = Category::allProductCategories();
            return view('report::product-stock-alert',compact('categories'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('product-stock-alert-report-access')){
                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                if (!empty($request->category_id)) {
                    $this->model->setCategoryID($request->category_id);
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
                    $row[] = $value->code;
                    $row[] = $value->category_name;
                    $row[] = $value->unit_name;
                    $row[] = number_format($value->qty,4,'.','') ?? 0;
                    $row[] = number_format($value->alert_quantity,4,'.','') ?? 0;
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
