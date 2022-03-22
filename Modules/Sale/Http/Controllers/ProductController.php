<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\WarehouseProduct;

class ProductController extends Controller
{
    public function product_autocomplete_search(Request $request)
    {
        if($request->ajax())
        {
            if(!empty($request->search))
            {
                $output = [];
                $search_text = $request->search;
                $data =  DB::table('warehouse_product as wp')
                ->leftjoin('products as p','wp.product_id','=','p.id')
                ->selectRaw('wp.qty,p.id,p.name,p.code')
                ->where([['wp.warehouse_id',$request->warehouse_id],['wp.qty','>',0]])
                ->where(function($q) use ($search_text){
                    $q->where('name','like','%'.$search_text.'%')
                    ->orWhere('code','like','%'.$search_text.'%');
                })
                ->orderBy('p.name','asc')
                ->get();
                
                if(!$data->isEmpty())
                {
                    foreach ($data as $value) {
                        $item['id']       = $value->id;
                        $item['code']     = $value->code;
                        $item['label']    = $value->name.' ('.$value->code.') - [Stock Avl. Qty: '.$value->qty.']';
                        $output[]         = $item;
                    }
                }else{
                    $output['code'] = '';
                    $output['batch_no'] = '';
                    $output['label'] = 'No Record Found';
                }
                return $output;
                
            }
        }
    }

    public function product_search(Request $request)
    {
        if($request->ajax())
        {
            $product = DB::table('warehouse_product as wp')
            ->join('products as p','wp.product_id','=','p.id')
            ->leftjoin('taxes as t','p.tax_id','=','t.id')
            ->where([
                ['wp.warehouse_id',$request->warehouse_id],
                ['wp.product_id',(int)$request->data['id']],
            ])
            ->selectRaw('wp.*,p.name,p.code,p.base_unit_id,p.base_unit_price as price,p.tax_method,t.name as tax_name,t.rate as tax_rate')
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
                $output['unit_name'] = implode(',',$unit_name).',';
                $output['unit_operator'] = implode(',',$unit_operator).',';
                $output['unit_operation_value'] = implode(',',$unit_operation_value).',';
                return $output;
            }
        }
    } 
    
    public function product_search_with_id(Request $request)
    {
        if($request->ajax())
        {
            $product = DB::table('warehouse_product as wp')
            ->join('products as p','wp.product_id','=','p.id')
            ->leftjoin('taxes as t','p.tax_id','=','t.id')
            ->where([
                ['wp.warehouse_id',$request->warehouse_id],
                ['wp.product_id',(int)$request->data],
            ])
            ->selectRaw('wp.*,p.name,p.code,p.base_unit_id,p.base_unit_price as price,p.tax_method,t.name as tax_name,t.rate as tax_rate')
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
                $output['unit_name'] = implode(',',$unit_name).',';
                $output['unit_operator'] = implode(',',$unit_operator).',';
                $output['unit_operation_value'] = implode(',',$unit_operation_value).',';
                return $output;
            }
        }
    }    
    public function product_select_search(Request $request)
    {
        $warehouse_id=0;
        if(!empty($request->warehouse_id)){
            $warehouse_id = $request->warehouse_id;
        }else{
            $warehouse_id = 1;
        }
            $output=[];
            $products = DB::table('warehouse_product as wp')
            ->join('products as p','wp.product_id','=','p.id')
            ->leftjoin('taxes as t','p.tax_id','=','t.id')
            ->leftjoin('units as u','p.base_unit_id','=','u.id')
            ->selectRaw('wp.*,p.name,p.code,p.image,p.base_unit_id,p.base_unit_price as price,p.tax_method,t.name as tax_name,t.rate as tax_rate,u.unit_name,u.unit_code')
            ->where([['wp.warehouse_id',$warehouse_id],['wp.qty','>',0]])
            ->orderBy('p.name','asc')
            ->get();
            
            if(!$products->isEmpty())
            {
                foreach($products as $row)
                {
                    $temp_array       = array();
                    $temp_array['id']             = $row->id;
                    $temp_array['name']           = $row->name;
                    $temp_array['code']           = $row->code;
                    $temp_array['base_unit_id']   = $row->base_unit_id;
                    $temp_array['base_unit_name'] = $row->unit_name;
                    $temp_array['net_unit_price'] = number_format($row->price,'2','.','');
                    $temp_array['stock_qty']      = $row->qty;
                    $temp_array['tax_name']       = $row->tax_name ? $row->tax_name : 'No Tax';
                    $temp_array['tax_rate']       = $row->tax_rate ? number_format($row->tax_rate,2,'.','') : number_format(0,2,'.','');
                    $temp_array['tax_method']     = $row->tax_method;
                    $temp_array['image']          = $row->image ? asset('storage/'.PRODUCT_IMAGE_PATH.$row->image) : asset('images/product.svg');
                    $temp_array['label']          = $row->name.' ('.$row->code.') - [Stock Avl. Qty: '.$row->qty.']';
                    $output[] = $temp_array;
                }
            }else{
                $temp_array['label']  = 'No data found!';
            }
            return $output;
    }
}
