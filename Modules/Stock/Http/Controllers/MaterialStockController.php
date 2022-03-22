<?php

namespace Modules\Stock\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Material\Entities\WarehouseMaterial;

class MaterialStockController extends BaseController
{

    public function __construct(WarehouseMaterial $model)
    {
        $this->model = $model;
    }
    public function index()
    {
        if(permission('material-stock-report-access')){
            $this->setPageData('Material Stock Report','Material Stock Report','fas fa-boxes',[['name' => 'Material Stock Report']]);
            $data = [
                'categories' => Category::with('warehouse_materials')->whereHas('warehouse_materials')->where([['type',1],['status',1]])->get(),
                'warehouses' => DB::table('warehouses')->where('status',1)->pluck('name','id')
            ];
            return view('stock::material.index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_material_stock_data(Request $request)
    {
        if($request->ajax())
        {
            $warehouse_id = $request->warehouse_id;
            $material_id = $request->material_id;
            $category_id = $request->category_id;
            $categories = Category::with('warehouse_materials')
            ->when($category_id, function($q) use ($category_id){
                $q->where('id',$category_id);
            })
            ->whereHas('warehouse_materials',function($query) use ($material_id,$warehouse_id){
                $query->where('warehouse_id',$warehouse_id)
                ->when($material_id, function($q) use ($material_id){
                    $q->where('material_id',$material_id);
                });
            });
            $categories = $categories->where([['type',1],['status',1]])->get();
            return view('stock::material.material-list',compact('categories','material_id','category_id'))->render();
        }
    }

    public function material_search(Request $request)
    {
        if ($request->ajax()) {
            if(!empty($request->search)){
                $data = Material::where('material_name', 'like','%'.$request->search.'%')->get();
                $output = array();
                if(!empty($data) && count($data) > 0)
                {
                    foreach($data as $row)
                    {
                        $temp_array             = array();
                        $temp_array['id']       = $row->id;
                        $temp_array['value']    = $row->material_name;
                        $temp_array['label']    = $row->material_name;
                        $output[]               = $temp_array;
                    }
                } else{
                    $output['value']            = '';
                    $output['label']            = 'No Record Found';
                }
                return $output; 
            }
        }
    }
}
