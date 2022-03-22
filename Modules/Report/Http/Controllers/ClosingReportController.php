<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\DailyClosing;
use Modules\Report\Http\Requests\ClosingFormRequest;

class ClosingReportController extends BaseController
{
    public function __construct(DailyClosing $model)
    {
        $this->model = $model;
    }

    public function index()
    {

        if(permission('closing-add')){
            $this->setPageData('Closing Account','Closing Account','fas fa-file',[['name' => 'Report','link'=>'javascript::void();'],['name' => 'Closing Account']]);
            $data = [
                'warehouses'      => Warehouse::allWarehouses(),
            ];
            return view('report::closing-report.form',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function closing_data(Request $request)
    {
        if ($request->ajax()) {
            $warehouse_id = $request->warehouse_id;
            $last_closing_amount_data = DailyClosing::select('closing_amount')->where('warehouse_id',$warehouse_id)->latest('date')->first();
            $cash_data=  DB::table('transactions as t')
                        ->selectRaw('SUM(debit) AS cash_in_amount, SUM(credit) AS cash_out_amount')
                        ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id' ,'=','coa.id')
                        ->where([
                            't.voucher_date' => date('Y-m-d'),
                            'coa.code'       => $this->coa_head_code('cash_in_hand'),
                            't.warehouse_id' => $warehouse_id]
                        )
                        ->first();
            $last_closing_amount  = $last_closing_amount_data ? $last_closing_amount_data->amount : 0;
            $cash_in = $cash_data ? ($cash_data->cash_in_amount ?? 0) : 0;
            $cash_out = $cash_data ? ($cash_data->cash_out_amount ?? 0) : 0;
            if($last_closing_amount)
            {
                $cash_in_hand = ($last_closing_amount + $cash_in) - $cash_out;
            }else{
                $cash_in_hand = $cash_in - $cash_out;
            }

            $data = [
                "last_day_closing" => $last_closing_amount,
                "cash_in"          => $cash_in,
                "cash_out"         => $cash_out,
                "cash_in_hand"     => $cash_in_hand,
            ];
            return response()->json($data);

        }
    }

    public function store(ClosingFormRequest $request)
    {
        if($request->ajax())
        {
            if(permission('closing-add')){
                $closing_data = DB::table('daily_closings')->where(['date' => date('Y-m-d'),'warehouse_id'=>$request->warehouse_id])->get()->count();
                if($closing_data > 0)
                {
                    $output = ['status' => 'error','message'=>'Already Closed Today'];
                }else{
                    $data = [
                        'warehouse_id'     => $request->warehouse_id,
                        'last_day_closing' => $request->last_day_closing ? $request->last_day_closing : 0,
                        'cash_in'          => $request->cash_in ? $request->cash_in : 0,
                        'cash_out'         => $request->cash_out ? $request->cash_out : 0,
                        'balance'         => $request->balance ? $request->balance : 0,
                        'transfer'         =>  0,
                        'closing_amount'   => $request->balance ? $request->balance : 0,
                        'date'             => date('Y-m-d'),
                        'thousands'        => $request->thousands ? $request->thousands : 0,
                        'five_hundred'     => $request->five_hundred ? $request->five_hundred : 0,
                        'two_hundred'      => $request->two_hundred ? $request->two_hundred : 0,
                        'hundred'          => $request->hundred ? $request->hundred : 0,
                        'fifty'            => $request->fifty ? $request->fifty : 0,
                        'twenty'           => $request->twenty ? $request->twenty : 0,
                        'ten'              => $request->ten ? $request->ten : 0,
                        'five'             => $request->five ? $request->five : 0,
                        'two'              => $request->two ? $request->two : 0,
                        'one'              => $request->one ? $request->one : 0,
                        'created_by'       => auth()->user()->name,
                    ];
                    // dd($data);
                    $result = DailyClosing::create($data);
                    $output = $this->store_message($result, null);
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }
    }

    public function report()
    {
        if(permission('closing-access')){
            $this->setPageData('Closing Report','Closing Report','fas fa-file-signature',[['name' => 'Report'],['name' => 'Closing Report']]);
            $data = [
                'warehouses'      => Warehouse::allWarehouses(),
            ];
            return view('report::closing-report.report',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->start_date)) {
                $this->model->setStartDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setEndDate($request->end_date);
            }
            if (!empty($request->warehouse_id)) {
                $this->model->setWarehouseID($request->warehouse_id);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('closing-view')){
                    $action .= ' <a class="dropdown-item view_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['View'].'</a>';
                }
                if(permission('closing-delete')){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->date . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }
                $row = [];
                $row[] = $no;
                if(empty(auth()->user()->warehouse_id)){
                    $row[] = $value->warehouse->name;
                }
                $row[] = date(config('settings.date_format'),strtotime($value->date));
                $row[] = number_format($value->cash_in,2, '.', '') ;
                $row[] = number_format($value->cash_out,2, '.', '');
                $row[] = number_format(($value->closing_amount),2, '.', '');
                $row[] = action_button($action);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('closing-view')){
                $closing   = $this->model->with('warehouse')->findOrFail($request->id);
                return view('report::closing-report.view-data',compact('closing'))->render();
            }
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('closing-delete')){
                $result   = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($result);
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
