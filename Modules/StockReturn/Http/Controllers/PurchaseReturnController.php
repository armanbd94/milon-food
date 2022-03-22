<?php

namespace Modules\StockReturn\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Material\Entities\Material;
use Modules\Supplier\Entities\Supplier;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\StockReturn\Entities\PurchaseReturn;
use Modules\StockReturn\Entities\PurchaseReturnMaterial;
use Modules\StockReturn\Http\Requests\PurchaseReturnRequest;

class PurchaseReturnController extends BaseController
{
    public function __construct(PurchaseReturn $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('purchase-return-access')){
            $this->setPageData('Purchase Return','Purchase Return','fas fa-undo-alt',[['name' => 'Purchase Return']]);
            $data = [
                'suppliers'  => Supplier::where('status',1)->get(),
            ];
            return view('stockreturn::purchase.index',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('purchase-return-access')){

                if (!empty($request->return_no)) {
                    $this->model->setReturnNo($request->return_no);
                }
                if (!empty($request->memo_no)) {
                    $this->model->setInvoiceNo($request->memo_no);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }
                if (!empty($request->supplier_id)) {
                    $this->model->setSupplierID($request->supplier_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('purchase-return-view')) {
                        $action .= ' <a class="dropdown-item view_data" href="'.route("purchase.return.show",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }

                    if (permission('purchase-return-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->return_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('purchase-return-bulk-delete')){
                        $row[] = row_checkbox($value->id);
                    }
                    $row[] = $no;
                    $row[] = $value->return_no;
                    $row[] = $value->memo_no;
                    $row[] = $value->supplier->name.($value->supplier->mobile ? ' ( '.$value->supplier->mobile.')' : '');
                    $row[] = date(config('settings.date_format'),strtotime($value->return_date));
                    $row[] = number_format($value->grand_total,2);
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

    public function store(PurchaseReturnRequest $request)
    {
        if($request->ajax()){
            if(permission('purchase-return-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $purchase_return_data = [
                        'return_no'        => 'PRINV-'.date('ym').rand(1,999),
                        'memo_no'          => $request->memo_no,
                        'warehouse_id'     => $request->warehouse_id,
                        'supplier_id'      => $request->supplier_id,
                        'total_price'      => $request->total_price,
                        'total_deduction'  => $request->total_deduction ? $request->total_deduction : null,
                        'tax_rate'         => $request->tax_rate ? $request->tax_rate : null,
                        'total_tax'        => $request->total_tax ? $request->total_tax : null,
                        'grand_total'      => $request->grand_total_price,
                        'reason'           => $request->reason,
                        'date'             => $request->purchase_date,
                        'return_date'      => $request->return_date,
                        'created_by'       => auth()->user()->name
                    ];

                    $purchase_return  = $this->model->create($purchase_return_data);
                    //purchase materials
                    $materials = [];
                    if($request->has('materials'))
                    {
                        foreach ($request->materials as $key => $value) {
                            if($value['return'] == 1){
                                $unit = Unit::where('unit_name',$value['unit'])->first();
                                if($unit->operator == '*'){
                                    $qty = $value['return_qty'] * $unit->operation_value;
                                }else{
                                    $qty = $value['return_qty'] / $unit->operation_value;
                                }

                                $materials[] = [
                                    'purchase_return_id' => $purchase_return->id,
                                    'memo_no'            => $request->memo_no,
                                    'material_id'        => $value['id'],
                                    'return_qty'         => $value['return_qty'],
                                    'unit_id'            => $unit ? $unit->id : null,
                                    'material_rate'      => $value['net_unit_cost'],
                                    'deduction_rate'     => $value['deduction_rate'] ? $value['deduction_rate'] : null,
                                    'deduction_amount'   => $value['deduction_amount'] ? $value['deduction_amount'] : null,
                                    'total'              => $value['total']
                                ];

                                $material = Material::find($value['id']);
                                if($material){
                                    $material->qty -= $qty;
                                    $material->update();
                                }
                                $warehouse_material = WarehouseMaterial::where(['warehouse_id' => $request->warehouse_id,'material_id'  => $value['id']])->first();
                                if($warehouse_material){
                                    $warehouse_material->qty -= $qty;
                                    $warehouse_material->update();
                                }
                                

                            }
                            
                        }
                        if(count($materials) > 0)
                        {
                            PurchaseReturnMaterial::insert($materials);
                        }
                    }

                    $supplier = Supplier::with('coa')->find($request->supplier_id);
                    $supplier_debit = array(
                        'chart_of_account_id' => $supplier->coa->id,
                        'warehouse_id'     => $request->warehouse_id,
                        'voucher_no'          => $request->memo_no,
                        'voucher_type'        => 'Return',
                        'voucher_date'        => $request->return_date,
                        'description'         => 'Supplier '.$supplier->name.' debit for Product Return Memo No. - ' . $request->memo_no,
                        'debit'               => $request->grand_total_price,
                        'credit'              => 0,
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    Transaction::create($supplier_debit);
                    $output  = $this->store_message($purchase_return,null);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(int $id)
    {
        if(permission('purchase-return-view')){
            $this->setPageData('Purchase Return Details','Purchase Return Details','fas fa-file',[['name' => 'Purchase Return Details']]);
            $purchase = $this->model->with('purchase','return_materials','supplier')->find($id);
            return view('stockreturn::purchase.details',compact('purchase'));
        }else{
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('purchase-return-delete'))
            {
                DB::beginTransaction();
                try {
    
                    $purchaseData = $this->model->with('purchase','return_materials')->find($request->id);
                    $memo_no = $purchaseData->memo_no;
    
                    if(!$purchaseData->return_materials->isEmpty())
                    {
                        foreach ($purchaseData->return_materials as  $return_material) {
                            $return_qty = $return_material->return_qty;
                            $purchase_unit = Unit::find($return_material->unit_id);
                            if($purchase_unit->operator == '*'){
                                $return_qty = $return_qty * $purchase_unit->operation_value;
                            }else{
                                $return_qty = $return_qty / $purchase_unit->operation_value;
                            }

                            $material = Material::find($return_material->material_id);
                            if($material){
                                $material->qty += $return_qty;
                                $material->update();
                            }

                            $warehouse_material = WarehouseMaterial::where(['warehouse_id' => $purchaseData->purchase->warehouse_id,'material_id'  => $return_material->material_id])->first();
                            if($warehouse_material){
                                $warehouse_material->qty += $return_qty;
                                $warehouse_material->update();
                            }

                        }
                        $purchaseData->return_materials()->delete();
                    }
                    Transaction::where(['voucher_no'=>$memo_no,'voucher_type'=>'Return'])->delete();
    
                    $result = $purchaseData->delete();
                    if($result)
                    {
                        $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                    }else{
                        $output = ['status' => 'error','message' => 'Failed to delete data'];
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('purchase-return-bulk-delete')){
                
                    DB::beginTransaction();
                    try {
                        foreach ($request->ids as $id) {
                            $purchaseData = $this->model->with('purchase','return_materials')->find($id);
                            $memo_no = $purchaseData->memo_no;
            
                            if(!$purchaseData->return_materials->isEmpty())
                            {
                                foreach ($purchaseData->return_materials as  $return_material) {
                                    $return_qty = $return_material->return_qty;
                                    $purchase_unit = Unit::find($return_material->unit_id);
                                    if($purchase_unit->operator == '*'){
                                        $return_qty = $return_qty * $purchase_unit->operation_value;
                                    }else{
                                        $return_qty = $return_qty / $purchase_unit->operation_value;
                                    }

                                    $material = Material::find($return_material->material_id);
                                    if($material){
                                        $material->qty += $return_qty;
                                        $material->update();
                                    }

                                    $warehouse_material = WarehouseMaterial::where(['warehouse_id' => $purchaseData->purchase->warehouse_id,'material_id'  => $return_material->material_id])->first();
                                    if($warehouse_material){
                                        $warehouse_material->qty += $return_qty;
                                        $warehouse_material->update();
                                    }

                                }
                                $purchaseData->return_materials()->delete();
                            }
                            Transaction::where(['voucher_no'=>$memo_no,'voucher_type'=>'Return'])->delete();
            
                            $result = $purchaseData->delete();
                            if($result)
                            {
                                $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                            }else{
                                $output = ['status' => 'error','message' => 'Failed to delete data'];
                            }
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $output = ['status'=>'error','message'=>$e->getMessage()];
                    }
                    return response()->json($output);
                
            }else{
                $output = $this->access_blocked();
            }
            return response()->json($output);
        }else{
            return response()->json($this->access_blocked());
        }
    }
}
