<?php

namespace Modules\Dealer\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Dealer\Entities\Dealer;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;

class DealerController extends BaseController
{
    public function __construct(Dealer $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('dealer-access')){
            $this->setPageData('Manage Dealer','Manage Dealer','far fa-handshake',[['name' => 'Manage Dealer']]);
            $warehouses = Warehouse::get();
            return view('dealer::index',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('dealer-access')){

                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                if (!empty($request->shop_name)) {
                    $this->model->setShopName($request->shop_name);
                }
                if (!empty($request->mobile)) {
                    $this->model->setMobile($request->mobile);
                }
                if (!empty($request->email)) {
                    $this->model->setEmail($request->email);
                }

                if (!empty($request->district_id)) {
                    $this->model->setDistrictID($request->district_id);
                }

                if (!empty($request->upazila_id)) {
                    $this->model->setUpazilaID($request->upazila_id);
                }

                if (!empty($request->status)) {
                    $this->model->setStatus($request->status);
                }


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('dealer-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("dealer.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('dealer-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("dealer.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }

                    if(permission('dealer-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    if(!empty($value->avatar))
                    {
                        $avatar =  "<img src='".asset("storage/".DEALER_AVATAR_PATH.$value->avatar)."' alt='".$value->name."' style='width:50px;'/>";
                    }else{
                        $avatar =  "<img src='".asset("images/male.svg")."' alt='Default Image' style='width:50px;'/>";
                    }
                    $row = [];
                    if(permission('dealer-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $avatar;
                    $row[] = $value->name.'<br><b>Mobile No.: </b>'.$value->mobile.($value->email ? '<br><b>Email: </b>'.$value->email : '');
                    $row[] = $value->shop_name;
                    if(empty(auth()->user()->warehouse_id))
                    {
                        $row[] = $value->warehouse->name;
                    }
                    $row[] = $value->district->name;
                    $row[] = $value->upazila->name;
                    $row[] = permission('dealer-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
                    $row[] = $this->model->balance($value->id);
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
        if(permission('dealer-add')){
            $this->setPageData('Add Dealer','Add Dealer','fas fa-exchange-alt',[['name' => 'Add Dealer']]);
            $data = [
                'warehouses'  =>  Warehouse::get(),
                'products'    => Product::with('unit')->get(),
            ];
            return view('dealer::form',$data);
        }else{
            return $this->access_blocked();
        }
    }

}
