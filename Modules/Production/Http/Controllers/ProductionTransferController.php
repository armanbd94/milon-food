<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Production\Entities\Production;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Production\Entities\ProductionCoupon;
use Modules\Production\Entities\ProductionProduct;
use Modules\Production\Http\Requests\ProductionRequest;
use Modules\Production\Entities\ProductionProductMaterial;

class ProductionTransferController extends BaseController
{
    public function __construct(Production $model)
    {
        $this->model = $model;
    }

    public function index(int $id)
    {
        if(permission('production-transfer')){
            $this->setPageData('Transfer Product','Transfer Product','fas fa-dolly-flatbed',[['name' => 'Transfer Product']]);
            $production = $this->model->with('products')->find($id);
            if($production)
            {
                return view('production::production.transfer',compact('production'));
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked();
        }
    }



}
