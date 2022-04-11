<?php

namespace Modules\SalesMen\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Modules\Account\Entities\Transaction;
use Modules\SalesMen\Entities\SalesmenMonthlyTarget;
use Modules\SalesMen\Http\Requests\MonthlyTargetFormRequest;

class SalesmanMonthlyTargetController extends BaseController
{
    public function __construct(SalesmenMonthlyTarget $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('sr-monthly-target-access')){
            // dd(date('m-t-Y'));
            $this->setPageData('SR Monthly Target','SR Monthly Target','fas fa-user-secret',[['name' => 'SR Monthly Target']]);
            $warehouses  = Warehouse::where('status',1)->get();
            return view('salesmen::monthly-traget.index',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->warehouse_id)) {
                $this->model->setWarehouseID($request->warehouse_id);
            }
            if (!empty($request->salesmen_id)) {
                $this->model->setSalesmenID($request->salesmen_id);
            }
            if (!empty($request->start_date)) {
                $this->model->setStartDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setEndDate($request->end_date);
            }


            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');

            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('sr-monthly-target-edit') && empty($value->closing_date)){
                $action .= ' <a class="dropdown-item edit_data"  data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('sr-monthly-target-view')){
                $action .= ' <a class="dropdown-item view_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['View'].'</a>';
                }
                if(permission('sr-monthly-target-delete') && empty($value->closing_date)){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->salesman_name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }

                $row = [];
                if(permission('sr-monthly-target-bulk-delete')){
                    $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                }

                $row[] = $no;
                $row[] = date('F, Y',strtotime($value->start_date));
                $row[] = $value->salesman_name.'<br><b>Phone No.: </b>'.$value->phone.'<br><b>MT Code: </b>'.$value->mtcode;
                $row[] = $value->warehouse_name;
                $row[] = number_format($value->target_value,2,'.',',');
                $row[] = number_format($value->achieved_value,2,'.',',');
                $row[] = number_format($value->commission_rate,2,'.',',');
                $row[] = number_format($value->commission_earned,2,'.',',');
                $row[] = $value->closing_date ? date('d-m-Y',strtotime($value->closing_date)) : '';
                $row[] = $value->created_by;
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
             $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    
    public function create()
    {
        if(permission('sr-monthly-target-add')){
            $this->setPageData('Add SR Monthly Target','Add SR Monthly Target','fas fa-user-secret',[['name' => 'Add SR Monthly Target']]);
            $warehouses      = Warehouse::allWarehouses();
            return view('salesmen::monthly-traget.create',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('sr-monthly-target-edit')){
                $monthly_target   = DB::table('salesmen_monthly_targets as smt')
                ->join('salesmen as s','smt.salesmen_id','=','s.id')
                ->join('warehouses as w','s.warehouse_id','=','w.id')
                ->selectRaw('smt.*,s.name as salesman_name,w.name as warehouse_name')
                ->where('smt.id',$request->id)
                ->first();
                $data = [
                    'id' => $monthly_target->id,
                    'month'          => date('F',strtotime($monthly_target->start_date)),
                    'warehouse_name' => $monthly_target->warehouse_name,
                    'salesman_name'  => $monthly_target->salesman_name,
                    'target_value'  => $monthly_target->target_value,
                    'commission_rate'  => $monthly_target->commission_rate,
                ];
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store(MonthlyTargetFormRequest $request)
    {
        if($request->ajax()){
            if(permission('sr-monthly-target-add')){
                DB::beginTransaction();
                try {
                    // dd($request->all());
                    $counter = 0;
                    $start_date      = date('Y-'.$request->month.'-01');
                    $end_date        = date('Y-'.$request->month.'-t');
                    foreach ($request->salesmen as $key => $value) {
                        $monthly_targets = [
                            'mtcode'          => 'SMT-'.date('ymdHis').($key+1),
                            'salesmen_id'     => $value['id'],
                            'target_value'    => $value['target_value'],
                            'commission_rate' => $value['commission_rate'],
                            'start_date'      => $start_date,
                            'end_date'        => $end_date,
                            'created_by'      => auth()->user()->name,
                            'created_at'      => date('Y-m-d'),
                        ];
                        $result = SalesmenMonthlyTarget::updateOrCreate(['salesmen_id'     => $value['id'],'start_date'      => $start_date],$monthly_targets);
                        if($result)
                        {
                            $counter++;
                        }
                    }
                    if($counter > 0)
                    {
                        $output = ['status' => 'success','message' => 'Data Has Been Saved Successfully'];
                    }else{
                        $output = ['status' => 'error','message' => 'Failed To Saved Data'];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function update(MonthlyTargetFormRequest $request)
    {
        if($request->ajax()){
            if(permission('sr-monthly-target-edit')){
                DB::beginTransaction();
                try {
                    $result = $this->model->find($request->update_id)->update([
                        'target_value'    => $request->target_value,
                        'commission_rate' => $request->commission_rate,
                        'modified_by'     => auth()->user()->name,
                        'updated_at'      => date('Y-m-d'),
                    ]);
                    if($result)
                    {
                        $output = ['status' => 'success','message' => 'Data Has Been Updated Successfully'];
                    }else{
                        $output = ['status' => 'error','message' => 'Failed To Update Data'];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('sr-monthly-target-delete')){
                $monthly_target   = $this->model->find($request->id);
                if($monthly_target)
                {
                    Transaction::where('voucher_no',$monthly_target->mtcode)->delete();
                    $result = $monthly_target->delete();
                    $output   = $this->delete_message($result);
                }else{
                    $output   = $this->delete_message($result=false);
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('sr-monthly-target-bulk-delete')){
                foreach ($request->ids as $id) {
                    $monthly_target   = $this->model->find($id);
                    if($monthly_target)
                    {
                        Transaction::where('voucher_no',$monthly_target->mtcode)->delete();
                        $result = $monthly_target->delete();
                        $output   = $this->delete_message($result);
                    }else{
                        $output   = $this->delete_message($result=false);
                    }
                }
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
