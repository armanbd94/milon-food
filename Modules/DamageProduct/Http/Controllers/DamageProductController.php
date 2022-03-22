<?php

namespace Modules\DamageProduct\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Customer;
use Modules\SalesMen\Entities\Salesmen;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\DamageProduct\Entities\Damage;
use Illuminate\Contracts\Support\Renderable;
use Modules\Product\Entities\WarehouseProduct;
use Modules\DamageProduct\Entities\DamageProduct;
use Modules\DamageProduct\Http\Requests\DamageProductRequest;

class DamageProductController extends BaseController
{
    public function __construct(Damage $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('damage-access')){
            $this->setPageData('Damage Product','Damage Product','fas fa-file',[['name' => 'Damage Product']]);
            $data = [
                'salesmen'    => DB::table('salesmen')->where('status',1)->select('name','id','phone')->get(),
                'locations'   => DB::table('locations')->where('status', 1)->get(),
            ];
            return view('damageproduct::index',$data);
        }else{
            return $this->access_blocked();
        }

    }
    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('damage-access')){

                if (!empty($request->damage_no)) {
                    $this->model->setDamageNo($request->damage_no);
                }
                if (!empty($request->memo_no)) {
                    $this->model->setMemoNo($request->memo_no);
                }
                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->salesmen_id)) {
                    $this->model->setSalesmenID($request->salesmen_id);
                }
                if (!empty($request->customer_id)) {
                    $this->model->setCustomerID($request->customer_id);
                }
                if (!empty($request->area_id)) {
                    $this->model->setAreaID($request->area_id);
                }
                if (!empty($request->upazila_id)) {
                    $this->model->setUpazilaID($request->upazila_id);
                }
                if (!empty($request->route_id)) {
                    $this->model->setRouteID($request->route_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    $action .= ' <a class="dropdown-item view_data" href="'.route("damage.product.show",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->damage_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->damage_no;
                    $row[] = $value->memo_no;
                    $row[] = $value->shop_name.' - '.$value->customer_name;
                    $row[] = $value->salesman_name;
                    $row[] = $value->upazila_name;
                    $row[] = $value->route_name;
                    $row[] = $value->area_name;
                    $row[] = $value->total_damage_items.'('.$value->total_damage_qty.')';
                    $row[] = date('d-M-Y',strtotime($value->damage_date));
                    $row[] = number_format($value->grand_total,2,'.','');
                    $row[] = number_format($value->deducted_sr_commission,2,'.','');
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

    public function product_search_with_id_for_damage(Request $request)
    {
        if($request->ajax())
        {
            $product = DB::table('sale_products as sp')
            ->join('products as p','sp.product_id','=','p.id')
            ->leftjoin('taxes as t','p.tax_id','=','t.id')
            ->where([
                ['sp.sale_id',$request->sale_id],
                ['sp.product_id',(int)$request->data],
            ])
            ->selectRaw('sp.*,p.name,p.code,p.base_unit_id,p.base_unit_price as price,p.tax_method,t.name as tax_name,t.rate as tax_rate')
            ->first();

            if($product)
            {
                $output['id']         = $product->product_id;
                $output['name']       = $product->name;
                $output['code']       = $product->code;
                $output['price']      = $product->price;
                $output['qty']        = $product->qty;
                $output['tax_name']   = $product->tax_name ?? 'No Tax';
                $output['tax_rate']   = $product->tax_rate ?? 0;
                $output['tax_method'] = $product->tax_method;

                $total_damage_qty = \DB::table('damage_products')
                                ->where([
                                    ['product_id',$product->product_id]
                                ])
                                ->sum('damage_qty');
                                $sold_qty = $product->qty - $total_damage_qty;

                $units = Unit::where('base_unit',$product->base_unit_id)->orWhere('id',$product->base_unit_id)->get();
                $unit_name            = [];
                $unit_operator        = [];
                $unit_operation_value = [];
                if($units)
                {
                    foreach ($units as $unit) {
                        if($product->base_unit_id == $unit->id)
                        {
                            array_unshift($unit_name,$unit->unit_name);
                            array_unshift($unit_operator,$unit->operator);
                            array_unshift($unit_operation_value,$unit->operation_value);
                        }else{
                            $unit_name           [] = $unit->unit_name;
                            $unit_operator       [] = $unit->operator;
                            $unit_operation_value[] = $unit->operation_value;
                        }
                    }
                }
                $output['sold_qty'] = $sold_qty;
                $output['unit_name'] = implode(',',$unit_name).',';
                $output['unit_operator'] = implode(',',$unit_operator).',';
                $output['unit_operation_value'] = implode(',',$unit_operation_value).',';
                return $output;
            }
        }
    }   

    public function store(DamageProductRequest $request)
    {
        if($request->ajax()){
            if(permission('damage-access')){
                // dd($request->all());
                DB::beginTransaction();
                try {

                    $sr_commission_rate = $request->sr_commission_rate ? $request->sr_commission_rate : 0;
                    if($sr_commission_rate > 0)
                    {
                        $deducted_commission = $request->grand_total_price * ($sr_commission_rate/100);
                    }else{
                        $deducted_commission = 0;
                    }
                    $warehouse_id = $request->warehouse_id;
                    $sale_damage_data = [
                        'damage_no'              => 'DINV-'.date('ymd').rand(1,999),
                        'memo_no'                => $request->memo_no,
                        'warehouse_id'           => $warehouse_id,
                        'customer_id'            => $request->customer_id,
                        'total_price'            => $request->total_price,
                        'tax_rate'               => $request->tax_rate ? $request->tax_rate : null,
                        'total_tax'              => $request->total_tax ? $request->total_tax : null,
                        'grand_total'            => $request->grand_total_price,
                        'deducted_sr_commission' => $deducted_commission,
                        'reason'                 => $request->reason,
                        'date'                   => $request->sale_date,
                        'damage_date'            => $request->damage_date,
                        'created_by'             => Auth::user()->name
                    ];

                    $damage  = $this->model->create($sale_damage_data);
                    //purchase products
                    $products = [];
                    if($request->has('products'))
                    {
                        foreach ($request->products as $key => $value) {
                                $unit = Unit::where('unit_name',$value['unit'])->first();
                                if($unit->operator == '*'){
                                    $qty = $value['damage_qty'] * $unit->operation_value;
                                }else{
                                    $qty = $value['damage_qty'] / $unit->operation_value;
                                }

                                $products[] = [
                                    'damage_id'     => $damage->id,
                                    'memo_no'            => $request->memo_no,
                                    'product_id'         => $value['id'],
                                    'damage_qty'         => $value['damage_qty'],
                                    'unit_id'            => $unit ? $unit->id : null,
                                    'product_rate'       => $value['net_unit_price'],
                                    'total'              => $value['total']
                                ];

                                // $warehouse_product = WarehouseProduct::where([
                                //     'warehouse_id'=> $warehouse_id,
                                //     'product_id'  => $value['id'],
                                //     ])->first();
                                // if($warehouse_product){
                                //     $warehouse_product->qty += $qty;
                                //     $warehouse_product->update();
                                // }                               
                            
                        }
                        if(count($products) > 0)
                        {
                            DamageProduct::insert($products);
                        }
                    }

                    $customer = Customer::with('coa')->find($request->customer_id);
                    $customer_credit = array(
                        'chart_of_account_id' => $customer->coa->id,
                        'warehouse_id'        => $warehouse_id,
                        'voucher_no'          => $request->memo_no,
                        'voucher_type'        => 'DAMAGE',
                        'voucher_date'        => $request->damage_date,
                        'description'         => 'Customer '.$customer->name.' credit for Product Damage Invoice NO- ' . $request->invoice_no,
                        'debit'               => 0,
                        'credit'              => $request->grand_total_price,
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    Transaction::create($customer_credit);                    

                    $salesmen = Salesmen::with('coa')->find($request->salesmen_id);
                    if($deducted_commission){
                        $sr_commission_info_damage = array(
                            'chart_of_account_id' => $salesmen->coa->id,
                            'warehouse_id'        => $warehouse_id,
                            'voucher_no'          => $request->memo_no,
                            'voucher_type'        => 'DAMAGE',
                            'voucher_date'        => $request->damage_date,
                            'description'         => 'Damage Total SR Commission For Invoice NO - ' . $request->memo_no . ' Sales Men ' .$salesmen->name,
                            'debit'               => $deducted_commission,
                            'credit'              => 0,
                            'posted'              => 1,
                            'approve'             => 1,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')
                        );
                        Transaction::create($sr_commission_info_damage);
                    }                    

                    $output  = $this->store_message($damage, null);
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
        if(permission('damage-access')){
            $this->setPageData('Damage Product Details','Damage Product Details','fas fa-file',[['name' => 'Damage Product Details']]);
            $damage = $this->model->with('damage_products','customer','sale')->find($id);
            if($damage)
            {
                return view('damageproduct::details',compact('damage'));
            }else{
                return redirect('damage.product')->with('error','No Data Available');
            }
        }else{
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('damage-access'))
            {
                DB::beginTransaction();
                try {
    
                    $damageData = $this->model->with('sale','damage_products')->find($request->id);
                    
                    if(!$damageData->damage_products->isEmpty())
                    {
                        
                        foreach ($damageData->damage_products as  $damage_product) {
                            $damage_qty = $damage_product->damage_qty;
                            $sale_unit = Unit::find($damage_product->unit_id);
                            if($sale_unit->operator == '*'){
                                $damage_qty = $damage_qty * $sale_unit->operation_value;
                            }else{
                                $damage_qty = $damage_qty / $sale_unit->operation_value;
                            }

                            // $warehouse_product = WarehouseProduct::where([
                            //     'warehouse_id'=> $damageData->sale->warehouse_id,
                            //     'product_id'=> $damage_product->product_id,
                            //     ])->first();
                            // if($warehouse_product){
                            //     $warehouse_product->qty -= $damage_qty;
                            //     $warehouse_product->update();
                            // }
                        }
                        $damageData->damage_products()->delete();
                    }
                    Transaction::where(['voucher_no'=>$damageData->memo_no,'voucher_type'=>'Damage'])->delete();
    
                    $result = $damageData->delete();
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
}
