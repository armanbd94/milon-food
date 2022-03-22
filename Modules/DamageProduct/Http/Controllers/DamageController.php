<?php

namespace Modules\DamageProduct\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class DamageController extends BaseController
{
    public function index()
    {
        if(permission('damage-access')){
            $this->setPageData('Damage','Damage','fas fa-undo-alt',[['name' => 'Damage']]);
            return view('damageproduct::form');
        }else{
            return $this->access_blocked();
        }
    }

    public function damage_sale(Request $request)
    {
        if(permission('damage-access')){
            $sale = Sale::with(['sale_products','customer:id,name,shop_name','warehouse:id,name','salesmen:id,name,phone','route:id,name','area:id,name'])
            ->where('memo_no',$request->get('memo_no'))->first();

            if($sale){
                $this->setPageData('Damage Product','Damage Product','fas fa-undo-alt',[['name' => 'Damage Product']]);

                $products = DB::table('warehouse_product as wp')
                    ->join('products as p','wp.product_id','=','p.id')
                    ->leftjoin('sale_products as sp','sp.product_id','=','p.id')
                    ->leftjoin('taxes as t','p.tax_id','=','t.id')
                    ->leftjoin('units as u','p.base_unit_id','=','u.id')
                    ->selectRaw('wp.*,sp.product_id,sp.sale_id as spro_id,p.name,p.code,p.image,p.base_unit_id,p.base_unit_price as price,p.tax_method,t.name as tax_name,t.rate as tax_rate,u.unit_name,u.unit_code')
                    ->where([['wp.warehouse_id',1],['wp.qty','>',0],['sp.sale_id','=',$sale->id]])
                    ->orderBy('p.name','asc')
                    ->get();
                $data = [
                    'sale'=>$sale,
                    'products'=>$products,
                ];
                return view('damageproduct::edit',$data);
            }else{
                return redirect('damage')->with(['status'=>'error','message'=>'No Data Found']);
            }
        }else{
            return $this->access_blocked();
        }
    }
}
