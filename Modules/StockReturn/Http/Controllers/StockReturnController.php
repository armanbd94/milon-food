<?php

namespace Modules\StockReturn\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Modules\Purchase\Entities\Purchase;
use App\Http\Controllers\BaseController;

class StockReturnController extends BaseController
{
    public function index()
    {
        if(permission('purchase-return-access')){
            $this->setPageData('Return','Return','fas fa-undo-alt',[['name' => 'Return']]);
            return view('stockreturn::form');
        }else{
            return $this->access_blocked();
        }
    }

    public function return_sale(Request $request)
    {
        if(permission('sale-return-access')){
            $sale = Sale::with(['sale_products','customer:id,name,shop_name','warehouse:id,name','salesmen:id,name,phone','route:id,name','area:id,name'])
            ->where('memo_no',$request->get('memo_no'))->first();

            if($sale){
                $this->setPageData('Sale Return','Sale Return','fas fa-undo-alt',[['name' => 'Sale Return']]);
                $data = [
                    'sale'=>$sale,
                ];
                return view('stockreturn::sale.edit',$data);
            }else{
                return redirect('return')->with(['status'=>'error','message'=>'No Data Found']);
            }
        }else{
            return $this->access_blocked();
        }
    }

    public function return_purchase(Request $request)
    {
        if(permission('purchase-return-access')){
            $purchase = Purchase::with('purchase_materials','supplier')
            ->where('memo_no',$request->get('memo_no'))->first();
            // dd($purchase);
            if($purchase){
                $this->setPageData('Purchase Return','Purchase Return','fas fa-undo-alt',[['name' => 'Purchase Return']]);
                $data = [
                    'purchase'=>$purchase,
                ];
                return view('stockreturn::purchase.edit',$data);
            }else{
                return redirect('return')->with(['status'=>'error','message'=>'No Purchase Data Found']);
            }
        }else{
            return $this->access_blocked();
        }
    }
    
}
