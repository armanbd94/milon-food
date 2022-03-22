<?php

namespace Modules\StockLedger\Http\Controllers;

use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;

class FinishedGoodsStockLedgerController extends BaseController
{
    public function index()
    {
        if(permission('product-ledger-access')){
            $this->setPageData('Product Stock Ledger','Product Stock Ledger','fas fa-file',[['name'=>'Product Stock Ledger']]);
            $products = Product::orderBy('id','asc')->get();
            return view('stockledger::finished-goods-ledger.index',compact('products'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-ledger-access')) {

                $product = Product::with('base_unit', 'category')->find($request->product_id);
                $start_date = $request->start_date ? $request->start_date . ' 00:00:01' : date('Y-m-01') . ' 00:00:01';
                $end_date = $request->end_date ? $request->end_date . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
                $date_period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), new DateTime($end_date));
                $ledger_data = [];
                $total_sold_qty = $total_sold_value = 0;
                $total_production_qty = $total_production_value = 0;
                $total_current_qty = $total_current_value = 0;
                foreach ($date_period as $key => $date) {
                    $previous_qty = $this->previous_data($request->product_id, $date->format('Y-m-d'));
                    $sold_data   = $this->sold_data($request->product_id,$date->format('Y-m-d'));
                    $production = $this->production_data($request->product_id,$date->format('Y-m-d'));
                    // $current_qty = $this->current_data($request->product_id,$date->format('Y-m-d'));
                    $current_qty = (($previous_qty + $production['qty']) - $sold_data['qty']);
                    $total_sold_qty += $sold_data['qty'];
                    $total_sold_value += $sold_data['value'];
                    $total_production_qty += $production['qty'];
                    $total_production_value += $product->base_unit_price * $production['qty'];

                    $total_current_qty = $current_qty;
                    $total_current_value = $product->base_unit_price * $current_qty;

                    $ledger_data[] = [
                        'date'             => $date->format('Y-m-d'),
                        'name'             => $product->name,
                        'category'         => $product->category->name,
                        'unit_name'        => $product->base_unit->unit_name,

                        'previous_cost'    => ($previous_qty > 0) ? $product->base_unit_price : 0,
                        'previous_qty'     => $previous_qty,
                        'previous_value'   => $product->base_unit_price * $previous_qty,

                        'sold_cost'        => $sold_data['cost'],
                        'sold_qty'         => $sold_data['qty'],
                        'sold_value'       => $sold_data['value'],
                        'sold_numbers'      => $sold_data['sold_numbers'],

                        'production_cost'  => ($production['qty'] > 0) ? $product->base_unit_price : 0,
                        'production_qty'   => $production['qty'],
                        'production_value' => $product->base_unit_price * $production['qty'],
                        'batch_numbers'      => $production['batch_numbers'],
                        'return_numbers'      => $production['return_numbers'],

                        'current_cost'     => ($current_qty > 0) ? $product->base_unit_price : 0,
                        'current_qty'      => $current_qty,
                        'current_value'    => $product->base_unit_price * $current_qty,
                    ];
                    
                    
                }
                // dd($ledger_data);
                $data = [
                    'ledger_data'            => $ledger_data,
                    'total_sold_qty'         => $total_sold_qty,
                    'total_sold_value'       => $total_sold_value,
                    'total_production_qty'   => $total_production_qty,
                    'total_production_value' => $total_production_value,
                    'total_current_qty'      => $total_current_qty,
                    'total_current_value'    => $total_current_value,
                ];
                // dd($ledger_data);
                return view('stockledger::finished-goods-ledger.data',$data)->render();
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

    protected function previous_data(int $id, $date)
    {

        $opening_stock_qty = 0;
        $opening_price = 0;
        $opening_date = '';
        $opening_stock = DB::table('adjustment_products')->where('product_id',$id)->first();
        if($opening_stock){
            $opening_stock_qty = $opening_stock->base_unit_qty ? $opening_stock->base_unit_qty : 0;
            $opening_price = $opening_stock->base_unit_price ? $opening_stock->base_unit_price : 0;
            if($opening_stock_qty){
              $opening_date = date('Y-m-d',strtotime($opening_stock->created_at));  
            }
            
        }
        $productionProducts = DB::table('productions as pro')
            ->selectRaw('pro.*,u.operator,u.operation_value,p.base_unit_price,pr.product_id')
            ->join('production_products as pr', 'pro.id', '=', 'pr.production_id')
            ->join('products as p', 'pr.product_id', '=', 'p.id')
            ->join('units as u', 'p.base_unit_id', '=', 'u.id')
            ->where('pr.product_id', $id)
            ->where('pro.status', 1)
            ->where('pro.production_status', 2)
            ->whereDate('pro.created_at', '<', $date)
            ->get();
        
        $total_product_old_qty = 0;
        if (!$productionProducts->isEmpty()) {
            foreach ($productionProducts as $product) {
                $total_product_old_qty += $product->total_fg_qty;
            }
        }

        $saleProducts = DB::table('sale_products as sp')
            ->selectRaw('sp.*,u.operator,u.operation_value,sp.net_unit_price')
            ->join('products as p', 'sp.product_id', '=', 'p.id')
            ->join('sales as s', 'sp.sale_id', '=', 's.id')
            ->join('units as u', 'sp.sale_unit_id', '=', 'u.id')
            ->where('sp.product_id', $id)
            ->whereDate('sp.created_at', '<', $date)
            ->get();
        
        $total_sold_qty = 0;
        if (!$saleProducts->isEmpty()) {
            foreach ($saleProducts as $product) {
                if ($product->operator == '*') {
                    $old_qty = $product->qty * $product->operation_value;
                } else {
                    $old_qty = $product->qty / $product->operation_value;
                }
                $total_sold_qty += $old_qty;
            }
        }

        $saleReturnProduct = DB::table('sale_return_products as srp')
            ->selectRaw('srp.*,u.operator,u.operation_value,sr.memo_no')
            ->join('products as m', 'srp.product_id', '=', 'm.id')
            ->join('sale_returns as sr', 'srp.sale_return_id', '=', 'sr.id')
            ->join('units as u', 'srp.unit_id', '=', 'u.id')
            ->where('srp.product_id', $id)
            ->whereDate('sr.created_at', '<', $date)
            ->get();
        
        $total_returned_product_qty = 0;
        if (!$saleReturnProduct->isEmpty()) {
            foreach ($saleReturnProduct as $product) {
                if ($product->operator == '*') {
                    $return_qty = $product->return_qty * $product->operation_value;
                } else {
                    $return_qty = $product->return_qty / $product->operation_value;
                }
                $total_returned_product_qty += $return_qty;
            }
        }
        if($opening_date == $date){
            $total_qty     = $opening_stock_qty;
        }elseif ($date >= $opening_date) {
            $total_qty =($opening_stock_qty + $total_product_old_qty + $total_returned_product_qty) - $total_sold_qty;
        }else{
            $total_qty     = 0; 
        }
        return $total_qty;
    }

    protected function sold_data(int $id, $date) : array
    {
        $product_data = [];
        $sold_number_list = [];
        $soldProducts = DB::table('sale_products as sp')
            ->selectRaw('sp.*,u.operator,u.operation_value,p.base_unit_price,p.tax_method,s.memo_no')
            ->join('products as p', 'sp.product_id', '=', 'p.id')
            ->join('sales as s', 'sp.sale_id', '=', 's.id')
            ->join('units as u', 'sp.sale_unit_id', '=', 'u.id')
            ->where('sp.product_id', $id)
            ->whereDate('sp.created_at',  $date)
            ->get();
        
        $total_product_old_price = $total_product_old_qty = 0;
        if (!$soldProducts->isEmpty()) {
            foreach ($soldProducts as $product) {
                if ($product->tax_method == 1) {
                    if ($product->operator == '*') {
                        $product_sold_price = ($product->net_unit_price + ($product->discount / $product->qty)) * $product->operation_value;
                    } elseif ($product->operator == '/') {
                        $product_sold_price = ($product->net_unit_price + ($product->discount / $product->qty)) / $product->operation_value;
                    }
                } else {
                    if ($product->operator == '*') {
                        $product_sold_price = (($product->total / $product->qty) + ($product->discount / $product->qty)) * $product->operation_value;
                    } elseif ($product->operator == '/') {
                        $product_sold_price = (($product->total / $product->qty) + ($product->discount / $product->qty)) / $product->operation_value;
                    }

                }
                if ($product->operator == '*') {
                    $old_qty = $product->qty * $product->operation_value;
                } else {
                    $old_qty = $product->qty / $product->operation_value;
                }
                $total_product_old_price += ($product_sold_price * $old_qty);
                $total_product_old_qty += $old_qty;
                $sold_number_list[] = $product->invoice_no;
            }
        }
        $old_cost = ($total_product_old_qty > 0) ? ($total_product_old_price / $total_product_old_qty) : 0;
        $sold_numbers = !empty($sold_number_list) ? array_unique($sold_number_list) : '';
        $product_data = [
            'cost' => number_format($old_cost, 4, '.', ''),
            'qty' => $total_product_old_qty,
            'value' => number_format(($old_cost * $total_product_old_qty), 4, '.', ''),
            'sold_numbers' => $sold_numbers
        ];
        return $product_data;
    }

    protected function production_data(int $id, $date)
    {
        $product_data = [];
        $batch_number_list = [];
        $return_number_list = [];
        $soldProducts = DB::table('productions as pro')
        ->selectRaw('pro.*,u.operator,u.operation_value,p.base_unit_price,pr.product_id')
        ->join('production_products as pr', 'pro.id', '=', 'pr.production_id')
        ->join('products as p', 'pr.product_id', '=', 'p.id')
        ->join('units as u', 'p.base_unit_id', '=', 'u.id')
        ->where('pr.product_id', $id)
            ->where('p.status', 1)
            ->where('pro.status', 1)
            ->where('pro.production_status', 2)
            ->whereDate('pro.created_at',  $date)
            ->get();
        
        $total_production_qty = 0;
        if (!$soldProducts->isEmpty()) {
            foreach ($soldProducts as $product) {
                $total_production_qty += $product->total_fg_qty;
                $batch_number_list[] = $product->batch_no;
            }
        }
        $saleReturnProduct = DB::table('sale_return_products as srp')
            ->selectRaw('srp.*,u.operator,u.operation_value,sr.memo_no')
            ->join('products as m', 'srp.product_id', '=', 'm.id')
            ->join('sale_returns as sr', 'srp.sale_return_id', '=', 'sr.id')
            ->join('units as u', 'srp.unit_id', '=', 'u.id')
            ->where('srp.product_id', $id)
            ->whereDate('sr.created_at',  $date)
            ->get();
        
        $total_returned_product_qty = 0;
        if (!$saleReturnProduct->isEmpty()) {
            foreach ($saleReturnProduct as $product) {
                if ($product->operator == '*') {
                    $return_qty = $product->return_qty * $product->operation_value;
                } else {
                    $return_qty = $product->return_qty / $product->operation_value;
                }
                $total_returned_product_qty += $return_qty;
                $return_number_list[] = $product->memo_no;
            }
        }
        $batch_numbers = !empty($batch_number_list) ? array_unique($batch_number_list) : '';
        $return_numbers = !empty($return_number_list) ? array_unique($return_number_list) : '';
        $product_data = [
            'qty' => ($total_production_qty + $total_returned_product_qty),
            'batch_numbers' => $batch_numbers,
            'return_numbers' => $return_numbers
        ];
        return $product_data;
    }

    protected function current_data(int $id, $date)
    {
        $opening_stock_qty = 0;
        $opening_price = 0;
        $opening_date = '';
        $opening_stock = DB::table('adjustment_products')->where('id',$id)->first();
        if($opening_stock){
            $opening_stock_qty = $opening_stock->base_unit_qty ? $opening_stock->base_unit_qty : 0;
            $opening_price = $opening_stock->base_unit_price ? $opening_stock->base_unit_price : 0;
            if($opening_stock_qty){
              $opening_date = date('Y-m-d',strtotime($opening_stock->created_at));  
            }
        }
        $currentStockProduct = DB::table('productions as pro')
            ->selectRaw('pro.*,u.operator,u.operation_value,p.base_unit_price,pr.product_id')
            ->join('production_products as pr', 'pro.id', '=', 'pr.production_id')
            ->join('products as p', 'pr.product_id', '=', 'p.id')
            ->join('units as u', 'p.base_unit_id', '=', 'u.id')
            ->where('pr.product_id', $id)
            ->where('pro.status', 1)
            ->where('pro.production_status', 2)
            ->whereDate('pro.created_at', '<=', $date)
            ->get();
        
        $total_product_current_qty = 0;
        if (!$currentStockProduct->isEmpty()) {
            foreach ($currentStockProduct as $product) {
                $total_product_current_qty += $product->total_fg_qty;
            }
        }

        $saleProducts = DB::table('sale_products as sp')
            ->selectRaw('sp.*,u.operator,u.operation_value,sp.net_unit_price')
            ->join('products as p', 'sp.product_id', '=', 'p.id')
            ->join('sales as s', 'sp.sale_id', '=', 's.id')
            ->join('units as u', 'sp.sale_unit_id', '=', 'u.id')
            ->where('sp.product_id', $id)
            ->whereDate('sp.created_at', '<=', $date)
            ->get();
        
        $total_sold_qty = 0;
        if (!$saleProducts->isEmpty()) {
            foreach ($saleProducts as $product) {
                if ($product->operator == '*') {
                    $old_qty = $product->qty * $product->operation_value;
                } else {
                    $old_qty = $product->qty / $product->operation_value;
                }
                $total_sold_qty += $old_qty;
            }
        }

        $saleReturnProduct = DB::table('sale_return_products as srp')
            ->selectRaw('srp.*,u.operator,u.operation_value,sr.memo_no')
            ->join('products as m', 'srp.product_id', '=', 'm.id')
            ->join('sale_returns as sr', 'srp.sale_return_id', '=', 'sr.id')
            ->join('units as u', 'srp.unit_id', '=', 'u.id')
            ->where('srp.product_id', $id)
            ->whereDate('sr.created_at', '<=', $date)
            ->get();
        
        $total_returned_product_qty = 0;
        if (!$saleReturnProduct->isEmpty()) {
            foreach ($saleReturnProduct as $product) {
                if ($product->operator == '*') {
                    $return_qty = $product->return_qty * $product->operation_value;
                } else {
                    $return_qty = $product->return_qty / $product->operation_value;
                }
                $total_returned_product_qty += $return_qty;
            }
        }
        if($opening_date == $date){
            $total_qty     = $opening_stock_qty;
        }elseif ($date >= $opening_date) {
            $total_qty =($opening_stock_qty + $total_product_current_qty + $total_returned_product_qty) - $total_sold_qty;
        }else{
            $total_qty     = 0; 
        }
        return $total_qty;
    }
}
