<?php

namespace Modules\StockLedger\Http\Controllers;

use App\Http\Controllers\BaseController;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\StockLedger\Entities\MaterialStockLedger;
use stdClass;

class MaterialStockLedgerController extends BaseController
{
    public function __construct(MaterialStockLedger $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('material-stock-ledger-access')) {
            $this->setPageData('Material Stock Ledger', 'Material Stock Ledger', 'fas fa-file', [['name' => 'Material Stock Ledger']]);
            $materials = Material::where(['status' => 1])->orderBy('id', 'asc')->get();
            return view('stockledger::material-ledger.index', compact('materials'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('material-stock-ledger-access')) {

                $material    = Material::with('unit', 'category')->find($request->material_id);
                $start_date  = $request->start_date ? $request->start_date . ' 00:00:01' : date('Y-m-01') . ' 00:00:01';
                $end_date    = $request->end_date ? $request->end_date . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
                $date_period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), new DateTime($end_date));
                $ledger_data = [];

                $total_purchase_qty = $total_purchase_value = 0;
                $total_production_qty = $total_production_value = 0;
                $total_current_qty = $total_current_value = 0;

                foreach ($date_period as $key => $date) {
                    $previous_data = $this->previous_data($request->material_id, $date->format('Y-m-d'));
                    $purchase_data   = $this->purchase_data($request->material_id,$date->format('Y-m-d'));
                    $production_data = $this->stock_out($request->material_id,$date->format('Y-m-d'));

                    $after_stock_out_current_qty = $previous_data['qty'] - ($production_data['qty']['production_material_qty'] + $production_data['qty']['returned_material_qty'] + $production_data['qty']['damage_material_qty']);
                    $after_stock_out_current_cost = $previous_data['qty'] * $previous_data['cost'];
                    $current_qty = $after_stock_out_current_qty + $purchase_data['qty'];
                    
                    if(!empty($production_data['datetime']) && !empty($purchase_data['datetime']))
                    {
                        if($purchase_data['datetime'] > $production_data['datetime'])
                        {
                            $current_cost = $purchase_data['new_unit_cost'];
                        }else{
                            $current_cost = $production_data['after_return_cost'];
                        }
                    }else{
                        $current_cost = ($current_qty > 0) ? (($after_stock_out_current_cost + $purchase_data['value']) / ($previous_data['qty'] + $purchase_data['qty'])) : 0;
                    }

                    $current_value = $current_qty * $current_cost;

                    $total_purchase_qty += $purchase_data['qty'];
                    $total_purchase_value += $purchase_data['value'];
                    $total_production_qty += $production_data['total_qty'];
                    $total_production_value += $production_data['value'];

                    $total_current_qty = $current_qty;
                    $total_current_value = $current_value;

                    $ledger_data[] = [
                        'date'             => $date->format('Y-m-d'),
                        'material_name'    => $material->material_name,
                        'category'         => $material->category->name,
                        'unit_name'        => $material->unit->unit_name,
                        'previous_cost'    => $previous_data['cost'],
                        'previous_qty'     => $previous_data['qty'],
                        'previous_value'   => $previous_data['value'],
                        'purchase_cost'    => $purchase_data['new_unit_cost'],
                        'purchase_qty'     => $purchase_data['qty'],
                        'purchase_value'   => $purchase_data['value'],
                        'purchase_numbers' => $purchase_data['purchase_numbers'],

                        'production_cost'     => $production_data['cost'],
                        'production_qty'      => $production_data['qty'],
                        'production_subtotal' => $production_data['subtotal'],
                        'production_value'    => $production_data['value'],
                        'batch_numbers'       => $production_data['batch_numbers'],
                        'return_numbers'      => $production_data['return_numbers'],
                        'damage_numbers'      => $production_data['damage_numbers'],
                        'current_cost'        => $current_cost,
                        'current_qty'         => $current_qty,
                        'current_value'       => $current_value,
                    ];
                }
                $data = [
                    'ledger_data'            => $ledger_data,
                    'total_purchase_qty'     => $total_purchase_qty,
                    'total_purchase_value'   => $total_purchase_value,
                    'total_production_qty'   => $total_production_qty,
                    'total_production_value' => $total_production_value,
                    'total_current_qty'      => $total_current_qty,
                    'total_current_value'    => $total_current_value,
                ];

                return view('stockledger::material-ledger.data',$data)->render();
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

    protected function previous_data(int $id, $date) : array
    {
        $material_data = [];

        //Opening Stock calculation
        $opening_stock_qty = 0;
        $opening_cost = 0;
        $opening_date = '';
        $opening_stock = DB::table('materials')->where('id',$id)->first();
        if($opening_stock){
            $opening_stock_qty = $opening_stock->opening_stock_qty ? $opening_stock->opening_stock_qty : 0;
            $opening_cost = $opening_stock->opening_cost ? $opening_stock->opening_cost : 0;
            if($opening_stock_qty){
              $opening_date = date('Y-m-d',strtotime($opening_stock->created_at));  
            }
            
        }
        $last_date = date('Y-m-d',strtotime('-2 day',strtotime($date)));
        $on_Date = date('Y-m-d',strtotime('-1 day',strtotime($date)));

        //Purchase Calculation
        $purchaseMaterial = DB::table('material_purchase as pm')
            ->selectRaw('pm.*,m.tax_method,p.shipping_cost,u.operator,u.operation_value,p.memo_no')
            ->join('materials as m', 'pm.material_id', '=', 'm.id')
            ->join('purchases as p', 'pm.purchase_id', '=', 'p.id')
            ->join('units as u', 'pm.purchase_unit_id', '=', 'u.id')
            ->where('pm.material_id', $id)
            ->whereDate('p.purchase_date', '<', $date)
            ->get();
            
        $total_material_purchased_cost = $total_purchased_net_cost = $total_purchased_material_qty = 0;
        $after_purchase_datetime = '';
        if (!$purchaseMaterial->isEmpty()) {
            foreach ($purchaseMaterial as $material) {
                if ($material->tax_method == 1) {
                    if ($material->operator == '*') {
                        $material_old_cost = ($material->net_unit_cost + ($material->discount / $material->qty)) / $material->operation_value;
                    } elseif ($material->operator == '/') {
                        $material_old_cost = ($material->net_unit_cost + ($material->discount / $material->qty)) * $material->operation_value;
                    }
                } else {
                    if ($material->operator == '*') {
                        $material_old_cost = (($material->total + ($material->discount / $material->qty)) / $material->qty) / $material->operation_value;
                    } elseif ($material->operator == '/') {
                        $material_old_cost = (($material->total + ($material->discount / $material->qty)) / $material->qty) * $material->operation_value;
                    }

                }
                if ($material->operator == '*') {
                    $old_qty = $material->received * $material->operation_value;
                } else {
                    $old_qty = $material->received / $material->operation_value;
                }
               
                $total_material_purchased_cost += ($material_old_cost * $old_qty);
                $total_purchased_material_qty += $old_qty;
                $total_purchased_net_cost = $material->new_unit_cost ? $material->new_unit_cost : $material->net_unit_cost;
                $after_purchase_datetime = $material->created_at;
            }
        }        
       

        //Production calculation
        $productionMaterial = DB::table('production_product_materials as pm')
            ->selectRaw('pm.*,p.batch_no')
            ->join('productions as p', 'pm.production_product_id', '=', 'p.id')
            ->where('pm.material_id', $id)
            ->where('p.status', 1)
            ->whereDate('p.start_date', '<',$date)
            ->get();
        
        $total_production_material_cost = $total_production_material_qty = $total_production_material_value = $total_damage_material_qty =  0;
        if (!$productionMaterial->isEmpty()) {
            foreach ($productionMaterial as $material) {
                $total_production_material_cost += $material->cost;
                $total_production_material_qty += $material->used_qty;
                $total_damage_material_qty = $material->damaged_qty;
                $total_production_material_value += ($material->cost * $material->qty);
            }
        }

        

        //Purchase Return Calculation
        $purchaseReturnMaterial = DB::table('purchase_return_materials as pm')
            ->selectRaw('pm.*,u.operator,u.operation_value,p.return_no')
            ->join('materials as m', 'pm.material_id', '=', 'm.id')
            ->join('purchase_returns as p', 'pm.purchase_return_id', '=', 'p.id')
            ->join('units as u', 'pm.unit_id', '=', 'u.id')
            ->where('pm.material_id', $id)
            ->whereDate('p.return_date', '<',$date)
            ->get();
        
        $total_returned_material_qty = $after_return_cost  = 0;
        $after_return_datetime = '';
        if (!$purchaseReturnMaterial->isEmpty()) {
            foreach ($purchaseReturnMaterial as $material) {
                if ($material->operator == '*') {
                    $return_qty = $material->return_qty * $material->operation_value;
                } else {
                    $return_qty = $material->return_qty / $material->operation_value;
                }
                $total_returned_material_qty += $return_qty;
                $after_return_cost = $material->material_rate;
                $after_return_datetime = $material->created_at;
            }
        }

        if($opening_date == $date){
            $material_cost = $opening_cost;
            $total_qty     = $opening_stock_qty;
        }elseif ($date >= $opening_date) {
            $total_qty = (($total_purchased_material_qty + $opening_stock_qty) - ($total_production_material_qty + $total_returned_material_qty + $total_damage_material_qty));
            $material_cost = $total_purchased_net_cost;
            if($material_cost == 0)
            {
                $material_cost = $opening_cost;
            }
        }else{
            $material_cost = 0;
            $total_qty     = 0; 
        }
        $cost = 0;
        if(!empty($after_return_datetime) && !empty($after_purchase_datetime))
        {
            if($after_return_datetime > $after_purchase_datetime)
            {
                $cost = $after_return_cost;
            }else{
                $cost = $material_cost;
            }
        }else{
            $cost = $material_cost;
        }
    
        
        $material_data = [
            'cost' => $cost,
            'qty' => $total_qty,
            'value' => $cost * $total_qty,
        ];
        return $material_data;
    }

    //Material Stock In Data
    protected function purchase_data(int $id, $date) : array
    {
        $material_data = [];
        $purchase_number_list = [];
        $purchaseMaterial = DB::table('material_purchase as pm')
            ->selectRaw('pm.*,m.tax_method,p.shipping_cost,u.operator,u.operation_value,p.memo_no')
            ->join('materials as m', 'pm.material_id', '=', 'm.id')
            ->join('purchases as p', 'pm.purchase_id', '=', 'p.id')
            ->join('units as u', 'pm.purchase_unit_id', '=', 'u.id')
            ->where('pm.material_id', $id)
            ->whereDate('p.purchase_date',  $date)
            ->get();
            $new_unit_cost = 0;
            $datetime = '';
        $total_purchased_cost =  $total_purchased_qty = 0;
        if (!$purchaseMaterial->isEmpty()) {
            foreach ($purchaseMaterial as $material) {
                if ($material->tax_method == 1) {
                    if ($material->operator == '*') {
                        $material_old_cost = ($material->net_unit_cost + ($material->discount / $material->qty)) / $material->operation_value;
                    } elseif ($material->operator == '/') {
                        $material_old_cost = ($material->net_unit_cost + ($material->discount / $material->qty)) * $material->operation_value;
                    }
                } else {
                    if ($material->operator == '*') {
                        $material_old_cost = (($material->total + ($material->discount / $material->qty)) / $material->qty) / $material->operation_value;
                    } elseif ($material->operator == '/') {
                        $material_old_cost = (($material->total + ($material->discount / $material->qty)) / $material->qty) * $material->operation_value;
                    }

                }
                if ($material->operator == '*') {
                    $old_qty = $material->received * $material->operation_value;
                } else {
                    $old_qty = $material->received / $material->operation_value;
                }
                $total_purchased_cost += ($material_old_cost * $old_qty);
                $total_purchased_qty += $old_qty;
                $purchase_number_list[] = $material->memo_no;
                $new_unit_cost = $material->new_unit_cost;
                $datetime = $material->created_at;
            }
        }
        $per_unit_cost = ($total_purchased_qty > 0) ? ($total_purchased_cost / $total_purchased_qty) : 0;
        $purchase_numbers = !empty($purchase_number_list) ? array_unique($purchase_number_list) : '';
        $material_data = [
            'cost' => $per_unit_cost,
            'qty' => $total_purchased_qty,
            'value' => $new_unit_cost * $total_purchased_qty,
            'purchase_numbers' => $purchase_numbers,
            'new_unit_cost' => $new_unit_cost,
            'datetime' => $datetime
        ];
        return $material_data;
    }

    //Material Stock Out Data
    protected function stock_out(int $id, $date) : array
    {
        $material_data = [];
        $batch_number_list = [];
        $return_number_list = [];
        $damage_number_list = [];
        $productionMaterial = DB::table('production_product_materials as pm')
            ->selectRaw('pm.*,p.batch_no,m.cost')
            ->join('productions as p', 'pm.production_product_id', '=', 'p.id')
            ->join('materials as m', 'pm.material_id', '=', 'm.id')
            ->where('pm.material_id', $id)
            ->where('p.status', 1)
            ->whereDate('p.start_date',  $date)
            ->get();
        
        $total_production_material_cost = $total_production_material_qty = $total_production_material_value =  0;
        $total_damage_material_qty = $total_damage_material_cost = $total_damage_material_value = 0;
        if (!$productionMaterial->isEmpty()) {
            foreach ($productionMaterial as $material) {
                $total_production_material_cost = $material->cost;
                $total_production_material_qty += $material->used_qty;
                $total_production_material_value += $material->cost * $material->used_qty;
                $batch_number_list[] = $material->batch_no;

                $total_damage_material_cost = $material->cost;
                $total_damage_material_qty += $material->damaged_qty;
                $total_damage_material_value += $material->cost * $material->damaged_qty;
                $damage_number_list[] = $material->batch_no;
            }
        }

        //Purchase Return Calculation
        $purchaseReturnMaterial = DB::table('purchase_return_materials as pm')
            ->selectRaw('pm.*,m.tax_method,u.operator,u.operation_value,p.return_no,m.cost')
            ->join('materials as m', 'pm.material_id', '=', 'm.id')
            ->join('purchase_returns as p', 'pm.purchase_return_id', '=', 'p.id')
            ->join('units as u', 'pm.unit_id', '=', 'u.id')
            ->where('pm.material_id', $id)
            ->whereDate('p.return_date',  $date)
            ->get();
        $after_return_cost = 0;
        $datetime = '';
        $total_returned_material_qty = $total_returned_material_cost = $total_returned_material_value = 0;
        if (!$purchaseReturnMaterial->isEmpty()) {
            foreach ($purchaseReturnMaterial as $material) {
                if ($material->operator == '*') {
                    $return_qty = $material->return_qty * $material->operation_value;
                } else {
                    $return_qty = $material->return_qty / $material->operation_value;
                }
                $total_returned_material_cost = $material->material_rate;
                $total_returned_material_qty += $return_qty;
                $total_returned_material_value += $material->material_rate * $return_qty;
                $return_number_list[] = $material->return_no;
                $after_return_cost = $material->material_rate;
                $datetime = $material->created_at;
            }
        }

        $batch_numbers = !empty($batch_number_list) ? array_unique($batch_number_list) : '';
        $return_numbers = !empty($return_number_list) ? array_unique($return_number_list) : '';
        $damage_numbers = !empty($damage_number_list) ? array_unique($damage_number_list) : '';
        

        $material_data = [
            'cost' => [
                'production_material_cost'     => $total_production_material_cost,
                'returned_material_cost' => $total_returned_material_cost,
                'damage_material_cost'         => $total_damage_material_cost,
            ],
            
            'qty' => [
                'production_material_qty'     => $total_production_material_qty,
                'returned_material_qty'       => $total_returned_material_qty,
                'damage_material_qty'         => $total_damage_material_qty,
            ],
            'subtotal' => [
                'production_material_cost'     => ($total_production_material_cost * $total_production_material_qty),
                'returned_material_cost'       => ($total_returned_material_cost * $total_returned_material_qty),
                'damage_material_cost'         => ($total_damage_material_cost * $total_damage_material_qty),
            ],
            'total_qty' => $total_production_material_qty + $total_returned_material_qty + $total_damage_material_qty,
            'value' => ($total_production_material_value+$total_returned_material_value+$total_damage_material_value),
            'batch_numbers' => $batch_numbers,
            'return_numbers' => $return_numbers,
            'damage_numbers' => $damage_numbers,
            'after_return_cost'=> $after_return_cost,
            'datetime'=>$datetime
        ];
        return $material_data;
    }

    
}
