<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Production\Entities\Production;
use Modules\Product\Entities\WarehouseProduct;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Production\Entities\ProductionCoupon;
use Modules\Production\Entities\ProductionProduct;
use Modules\Production\Http\Requests\ProductionRequest;
use Modules\Production\Entities\ProductionProductMaterial;

class ProductionController extends BaseController
{
    public function __construct(Production $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('production-access')){
            $this->setPageData('Manage Production','Manage Production','fas fa-industry',[['name' => 'Manage Production']]);
            $warehouses = DB::table('warehouses')->where('status',1)->pluck('name','id');
            return view('production::production.index',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('production-access')){

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
                if (!empty($request->status)) {
                    $this->model->setStatus($request->status);
                }
                if (!empty($request->production_status)) {
                    $this->model->setProductionStatus($request->production_status);
                }
                if (!empty($request->transfer_status)) {
                    $this->model->setTransferStatus($request->transfer_status);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('production-approve')  && $value->status == 2){
                        $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '" data-name="' . $value->batch_no . '" data-status="' . $value->status . '"><i class="fas fa-toggle-on text-info mr-2"></i> Approve Status</a>';
                    }
                    if(permission('production-edit') && $value->status == 2){
                        $action .= ' <a class="dropdown-item" href="'.route("production.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('production-operation') && $value->status == 1 && $value->production_status != 3 ){
                        $action .= ' <a class="dropdown-item" href="'.url("production/operation/".$value->id).'"><i class="fas fa-toolbox text-success mr-2"></i> Operation</a>';
                    }
                    if(permission('production-view')){
                        $action .= ' <a class="dropdown-item" href="'.url("production/view/".$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('production-delete') && $value->production_status != 3 && $value->transfer_status == 1){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    // if(permission('production-transfer') && $value->status == 1 && $value->production_status == 3 && $value->transfer_status == 1){
                    //     $action .= ' <a class="dropdown-item" href="'.url("production/transfer/".$value->id).'"><i class="fas fa-dolly-flatbed text-dark mr-2"></i> Transfer</a>';
                    // }

                    $row = [];
                    $row[] = $no;
                    $row[] = $value->batch_no;
                    $row[] = $value->warehouse->name;
                    $row[] = date('d-M-Y',strtotime($value->start_date));
                    $row[] = $value->end_date ? date('j-F-Y',strtotime($value->end_date)) : '-';
                    $row[] = $value->item;
                    $row[] = APPROVE_STATUS_LABEL[$value->status];
                    $row[] = PRODUCTION_STATUS_LABEL[$value->production_status];
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function create()
    {
        if(permission('production-add')){
            $this->setPageData('Add Production','Add Production','fas fa-industry',[['name' => 'Add Production']]);
            $last_batch_no = $this->model->select('batch_no')->orderBy('id','desc')->first();
            $data = [
                'products'   => DB::table('products')->where('status', 1)->pluck('name','id'),
                'warehouses' => Warehouse::activeWarehouses(),
                'batch_no'   => $last_batch_no ? $last_batch_no->batch_no + 1 : '1001'
            ];
            return view('production::production.create',$data);
        }else{
            return $this->access_blocked(); 
        }
    }

    public function check_material_stock(ProductionRequest $request)
    {
        if($request->ajax())
        {
            $production_materials = [];
            $below_qty = 0;
            if($request->has('production')){
                $materials = [];
                foreach($request->production as $product)
                {
                    if (!empty($product['materials']) && count($product['materials']) > 0) {
                        foreach ($product['materials'] as $value) {
                            if(!empty($materials)){
                                $key = array_search($value['material_id'], array_column($materials, 'material_id'));
                                if($materials[$key]['material_id'] == $value['material_id'] )
                                {
                                    $materials[$key]['qty'] += $value['qty'];
                                }else{
                                    $materials[] = [
                                        'material_id' => $value['material_id'],
                                        'qty' => $value['qty'],
                                    ];
                                }
                            }else{
                                $materials[] = [
                                    'material_id' => $value['material_id'],
                                    'qty' => $value['qty'],
                                ];
                            }
                            
                        }

                    }
                }
                if(!empty($materials)){
                    foreach($materials as $item){
                        $material = Material::with('unit')->find($item['material_id']);
                        $material_stock = WarehouseMaterial::where([['material_id',$item['material_id']],['warehouse_id',1]])->first();
                        $stock_qty = 0;
                        $background = '';
                        if($material_stock){
                            $stock_qty = $material_stock->qty;
                            if($stock_qty < $item['qty']){
                                $background = 'bg-danger';
                                $below_qty++;
                            }
                        }else{
                            $background = 'bg-danger';
                            $below_qty++;
                        }
                        $production_materials[] = [
                            'material_name' => $material ? $material->material_name.' ('.$material->material_code.')' : '',
                            'type'          => $material ? MATERIAL_TYPE[$material->type] : '',
                            'unit_name'     => $material ? $material->unit->unit_name.' ('.$material->unit->unit_code.')' : '',
                            'stock_qty'     => $stock_qty,
                            'qty'           => $item['qty'],
                            'background'    => $background,
                        ];
                    }
                }
                
            }
            if($below_qty > 0){
                $data = [
                    'materials'    =>  $production_materials,
                    'below_qty'    => $below_qty
                ];
    
                return view('production::production.view-data',$data)->render();
            }else{
                return ['status'=>'success'];
            }
           
        }
    }

    public function store(ProductionRequest $request)
    {
        if ($request->ajax()) {
            if (permission('production-add')) {
                if($request->has('production')){
                    DB::beginTransaction();
                    try {
                        $production = $this->model->create([
                            'batch_no'     => $request->batch_no,
                            'warehouse_id' => $request->warehouse_id,
                            'item'         => count($request->production),
                            'start_date'   => $request->start_date,
                            'created_by'   => auth()->user()->name
                        ]);
                        if($production)
                        {
                            foreach($request->production as $product)
                            {
                                $product_data = [
                                    'production_id'   => $production->id,
                                    'product_id'      => $product['product_id'],
                                    'year'            => $product['year'],
                                    'mfg_date'        => $product['mfg_date'],
                                    'exp_date'        => $product['exp_date'],
                                ];
                                $productData = ProductionProduct::create($product_data);
                                if($product){
                                    $production_product = ProductionProduct::with('materials')->find($productData->id);
                                    $materials = [];
                                    if (!empty($product['materials']) && count($product['materials']) > 0) {
                                        foreach ($product['materials'] as $value) {
                                            $materials[$value['material_id']] = [
                                                'unit_id' => $value['unit_id'],
                                                'qty'     => $value['qty'],
                                                'cost'    => $value['cost'],
                                                'total'   => $value['total'],
                                            ];
                                        }
                                        $production_product->materials()->sync($materials);
                                    }

                                }
                            }
                            $output = ['status' => 'success','message' => 'Data has been saved successfully'];
                        }else{
                            $output = ['status' => 'error','message' => 'Failed to save data'];
                        }
                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        $output = ['status' => 'error', 'message' => $th->getMessage()];
                    }
                }else{
                    $output = ['status' => 'error','message' => 'Fill up the input field properly'];
                }
                return response()->json($output);
            } else {
                return response()->json($this->unauthorized());
            }
        }
    }

    public function show(int $id)
    {
        if(permission('production-view')){
            $production = $this->model->with(['warehouse:id,name','products'])->find($id);
            if($production)
            {
                $this->setPageData('Production Details','Production Details','fas fa-industry',[['name' => 'Production Details']]);
                return view('production::production.view',compact('production'));
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked(); 
        }
    }

    public function edit(int $id)
    {
        if(permission('production-view')){
            $production = $this->model->with(['products'])->find($id);
            if($production)
            {
                $this->setPageData('Production Edit','Production Edit','fas fa-industry',[['name' => 'Production Edit']]);
                $warehouses = Warehouse::activeWarehouses();
                return view('production::production.edit',compact('production','warehouses'));
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked(); 
        }
    }

    public function update(ProductionRequest $request)
    {
        if ($request->ajax()) {
            // dd($request->all());
            if (permission('production-edit')) {
                DB::beginTransaction();
                try {
                    if($request->has('production')){
                        $production = $this->model->find($request->update_id)->update([
                            'warehouse_id' => $request->warehouse_id,
                            'start_date'   => $request->start_date,
                            'modified_by'  => auth()->user()->name
                        ]);
                        if($production)
                        {
                            foreach($request->production as $product)
                            {
                                $production_product = ProductionProduct::find($product['production_product_id']);
                                if($production_product)
                                {
                                    $production_product->update([
                                        'product_id'      => $product['product_id'],
                                        'year'            => $product['year'],
                                        'mfg_date'        => $product['mfg_date'],
                                        'exp_date'        => $product['exp_date'],
                                    ]);

                                    if (!empty($product['materials']) && count($product['materials']) > 0) {
                                        foreach ($product['materials'] as $material) {
                                            $production_material = ProductionProductMaterial::find($material['production_material_id']);
                                            if($production_material)
                                            {
                                                $production_material->update([
                                                    'qty'     => $material['qty'],
                                                    'cost'    => $material['cost'],
                                                    'total'   => $material['total'],
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                            $output = ['status' => 'success','message' => 'Data Updated Successfully'];
                        }else{
                            $output = ['status' => 'error','message' => 'Failed to Update Data'];
                        }
                    }else{
                        $output = ['status' => 'error','message' => 'Failed to Update Data'];
                    }
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollback();
                    $output = ['status' => 'error', 'message' => $th->getMessage()];
                }
                return response()->json($output);
            } else {
                return response()->json($this->unauthorized());
            }
        }
    }

    public function change_status(Request $request)
    {
        if ($request->ajax()) {
            if (permission('production-approve')) {
                if ($request->approve_status) {
                    DB::beginTransaction();
                    try {

                        $productionData = $this->model->find($request->production_id);
                        $productionData->status = $request->approve_status;
                        if ($request->approve_status == 1) {
                            $productionData->production_status = 2;
                        }
                        $productionData->modified_by = auth()->user()->name;
                        $productionData->updated_at = date('Y-m-d');
                        if ($productionData->update()) {
                            if ($request->approve_status == 1) {
                                $production_materials = DB::table('production_product_materials as ppm')
                                                        ->join('production_products as pp','ppm.production_product_id','=','pp.id')
                                                        ->join('productions as p','pp.production_id','=','p.id')
                                                        ->where('p.id',$request->production_id)
                                                        ->select('ppm.material_id','ppm.qty')
                                                        ->get();
                                
                                if($production_materials){
                                    foreach ($production_materials as $material) {
                                        
                                        $warehouse_material = WarehouseMaterial::where([
                                            ['warehouse_id', 1],
                                            ['material_id', $material->material_id],['qty','>',0]
                                        ])->first();
                                        if ($warehouse_material) {
                                            $warehouse_material->qty -= $material->qty;
                                            $warehouse_material->update();
                                        }
    
                                        //Remove qty from material
                                        $material_data = Material::find($material->material_id);
                                        if ($material_data) {
                                            $material_data->qty -= $material->qty;
                                            $material_data->update();
                                        }
                                    }
                                }
                            }
                            $output = ['status' => 'success', 'message' => 'Production Data Approved Successfully'];
                        } else {
                            $output = ['status' => 'error', 'message' => 'Failed To Approve Production Data'];
                        }
                        
                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        $output = ['status' => 'error', 'message' => $th->getMessage()];
                    }
                } else {
                    $output = ['status' => 'error', 'message' => 'Please select status'];
                }
                return response()->json($output);
            }
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('production-delete')) {
                DB::beginTransaction();
                try {
                    $productionData = $this->model->with('products')->find($request->id);
                    if (!$productionData->products->isEmpty()) {
                        foreach ($productionData->products as $item) {
                            $product = ProductionProduct::with('materials')->find($item->id);
                            if($product)
                            {
                                if(!$product->materials->isEmpty())
                                {
                                    if ($productionData->status == 1 && $productionData->production_status != 3) {
                                        foreach ($product->materials as $value) {
                                            $warehouse_material = WarehouseMaterial::where([
                                                ['warehouse_id', 1],
                                                ['material_id', $value->id],
                                            ])->first();
                                            if ($warehouse_material) {
                                                $warehouse_material->qty += $value->pivot->qty;
                                                $warehouse_material->update();
                                            }
            
                                            //Remove qty from material
                                            $material_data = Material::find($value->id);
                                            if ($material_data) {
                                                $material_data->qty += $value->pivot->qty;
                                                $material_data->update();
                                            }
                                        }
                                    }elseif ($productionData->status == 1 && $productionData->production_status == 3) {
                                        $warehouse_product = WarehouseProduct::where([
                                            ['warehouse_id', $productionData->warehouse_id],
                                            ['product_id', $product->product_id]
                                        ])->first();
                                        if($warehouse_product)
                                        {
                                            $warehouse_product->qty -= $product->base_unit_qty;
                                            $warehouse_product->update();
                                        }
                                        foreach ($product->materials as $value) {
                                            $used_qty = $value->pivot->used_qty + ($value->pivot->damaged_qty ? $value->pivot->damaged_qty : 0);
                                            $warehouse_material = WarehouseMaterial::where([
                                                ['warehouse_id', 1],
                                                ['material_id', $value->id],
                                            ])->first();
                                            if ($warehouse_material) {
                                                $warehouse_material->qty += $used_qty;
                                                $warehouse_material->update();
                                            }
            
                                            //Remove qty from material
                                            $material_data = Material::find($value->id);
                                            if ($material_data) {
                                                $material_data->qty += $used_qty;
                                                $material_data->update();
                                            }
                                        }
                                    }
                                    $product->materials()->detach();
                                }
                                
                                $product->delete();
                            }
                        }
                    }
                    $result = $productionData->delete();
                    $output = $this->delete_message($result);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollback();
                    $output = ['status' => 'error', 'message' => $th->getMessage()];
                }
                return response()->json($output);
            } else {
                return response()->json($this->unauthorized());
            }
        }
    }



    public function product_material_list(Request $request)
    {
        $tab = $request->tab;
        $materials = DB::table('product_material as pm')
                    ->join('materials as m','pm.material_id','=','m.id')
                    ->leftJoin('units as u','m.unit_id','=','u.id')
                    ->select('pm.*','m.material_name','m.material_code','m.cost','m.qty','m.type','m.unit_id','u.unit_name','u.unit_code')
                    ->where('pm.product_id',$request->product_id)
                    ->get();
        return view('production::production.materials',compact('materials','tab'))->render();
    }
}
