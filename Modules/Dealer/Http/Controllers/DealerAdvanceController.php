<?php

namespace Modules\Dealer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Dealer\Entities\Dealer;
use App\Http\Controllers\BaseController;
use Modules\Dealer\Entities\DealerAdvance;
use Modules\Dealer\Http\Requests\DealerAdvanceFormRequest;

class DealerAdvanceController extends BaseController
{
    public function __construct(DealerAdvance $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('dealer-advance-access')){
            $this->setPageData('Dealer Advance','Dealer Advance','fas fa-hand-holding-usd',[['name'=>'Dealer','link'=>route('dealer')],['name'=>'Dealer Advance']]);
            $warehouse_id = auth()->user()->warehouse_id;
            $dealers = Dealer::with('coa','warehouse')->where('status', 1)->when($warehouse_id,function($q) use ($warehouse_id){
                $q->where('warehouse_id',$warehouse_id);
            })->get();
            $warehouses = DB::table('warehouses')->where('status', 1)->pluck('name','id');
            return view('dealer::advance.index',compact('dealers','warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->dealer_id)) {
                $this->model->setDealerID($request->dealer_id);
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
                if(permission('dealer-advance-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('dealer-advance-delete')){
                $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->name . ' advance ">'.self::ACTION_BUTTON['Delete'].'</a>';
                }
                $account = $this->account_data($value->voucher_no);

                if($account->coa->parent_name == 'Cash & Cash Equivalent'){
                    $payment_method = 'Cash';
                }elseif ($account->coa->parent_name == 'Cash At Bank') {
                    $payment_method = 'Cheque';
                }elseif ($account->coa->parent_name == 'Cash At Mobile Bank') {
                    $payment_method = 'Mobile Bank';
                }
                $row = [];

                $row[] = $no;
                $row[] = $value->dealer_name;
                $row[] = $value->shop_name;
                $row[] = $value->mobile;
                $row[] = $value->warehouse_name;
                $row[] = ($value->debit != 0) ? 'Receive' : 'Payment' ;
                $row[] = ($value->debit != 0) ? number_format($value->debit,2,'.',',') : number_format($value->credit,2,'.',',');
                $row[] = date(config('settings.date_format'),strtotime($value->created_at));
                $row[] = $payment_method;
                $row[] = $account->coa->name;
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    private function account_data(string $voucher_no) : object
    {
        return $this->model->with('coa')->where('voucher_no',$voucher_no)->orderBy('id','desc')->first();

    }

    public function store_or_update_data(DealerAdvanceFormRequest $request)
    {
        if($request->ajax()){
            if(permission('dealer-advance-add') || permission('dealer-advance-edit')){
                DB::beginTransaction();
                try {
                    // dd($request->all());
                    if(empty($request->id)){
                        $result = $this->advance_add($request->type,$request->amount,$request->dealer_coaid,$request->dealer_name,$request->payment_method,$request->account_id,$request->cheque_number,$request->warehouse_id);
                        $output = $this->store_message($result, $request->id);
                    }else{
                        $result = $this->advance_update($request->id,$request->type,$request->amount,$request->dealer_coaid,$request->dealer_name,$request->payment_method,$request->account_id,$request->cheque_number,$request->warehouse_id);
                        $output = $this->store_message($result, $request->id);
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

    private function advance_add(string $type, $amount, int $dealer_coa_id, string $dealer_name,int $payment_method, int $account_id, string $cheque_number = null,$warehouse_id) {
        if(!empty($type) && !empty($amount) && !empty($dealer_coa_id) && !empty($dealer_name)){
            $transaction_id = generator(10);

            $dealer_accledger = array(
                'chart_of_account_id' => $dealer_coa_id,
                'warehouse_id'        => $warehouse_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'Advance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => 'Dealer advance from '.$dealer_name,
                'debit'               => ($type == 'debit') ? $amount : 0,
                'credit'              => ($type == 'credit') ? $amount : 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
            if($payment_method == 1){
                $note = 'Cash in Hand For '.$dealer_name;
            }elseif ($payment_method == 2) {
                $note = $cheque_number;
            }else{
                $note = 'Cash at Mobile Bank For '.$dealer_name;
            }
            $cc = array(
                'chart_of_account_id' => $account_id,
                'warehouse_id'        => $warehouse_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'Advance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => $note,
                'debit'               => ($type == 'credit') ? $amount : 0,
                'credit'              => ($type == 'debit') ? $amount : 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 

            return $this->model->insert([
                $dealer_accledger,$cc
            ]);
        }
    }

    private function advance_update(int $transaction_id, string $type, $amount, int $dealer_coa_id, string $dealer_name,int $payment_method, int $account_id, string $cheque_number = null,$warehouse_id) {
        if(!empty($type) && !empty($amount) && !empty($dealer_coa_id) && !empty($dealer_name)){

            $dealer_advance_data = $this->model->find($transaction_id);

            $voucher_no = $dealer_advance_data->voucher_no;

            $updated = $dealer_advance_data->update([
                'warehouse_id'        => $warehouse_id,
                'description'         => 'Supplier Advance For '.$dealer_name,
                'debit'               => ($type == 'debit') ? $amount : 0,
                'credit'              => ($type == 'credit') ? $amount : 0,
                'modified_by'         => auth()->user()->name,
                'updated_at'          => date('Y-m-d H:i:s')
            ]);
            if($updated)
            {
                if($payment_method == 1){
                    $note = 'Cash in Hand For '.$dealer_name;
                }elseif ($payment_method == 2) {
                    $note = $cheque_number;
                }else{
                    $note = 'Cash at Mobile Bank For '.$dealer_name;
                }
                $account = $this->model->where('voucher_no', $voucher_no)->orderBy('id','desc')->first();
                if($account){
                    $account->update([
                        'chart_of_account_id' => $account_id,
                        'warehouse_id'        => $warehouse_id,
                        'description'         => $note,
                        'debit'               => ($type == 'credit') ? $amount : 0,
                        'credit'              => ($type == 'debit') ? $amount : 0,
                        'modified_by'         => auth()->user()->name,
                        'updated_at'          => date('Y-m-d H:i:s')
                    ]);
                }
                return true;
            }else{
                return false;
            }
           
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('dealer-advance-edit')){
                $data   = $this->model->select('transactions.*','coa.id as coa_id','coa.code','c.id as dealer_id')
                ->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
                ->join('dealers as c','coa.dealer_id','c.id')
                ->where('transactions.id',$request->id)
                ->first();
                $account = $this->account_data($data->voucher_no);
                if($account->coa->parent_name == 'Cash & Cash Equivalent'){
                    $payment_method = 1;
                }elseif ($account->coa->parent_name == 'Cash At Bank') {
                    $payment_method = 2;
                }elseif ($account->coa->parent_name == 'Cash At Mobile Bank') {
                    $payment_method = 3;
                }
                $output = []; //if data found then it will return data otherwise return error message
                if($data){
                    $output = [
                        'id'             => $data->id,
                        'warehouse_id'   => $data->warehouse_id,
                        'dealer_id'      => $data->dealer_id,
                        'type'           => ($data->debit != 0) ? 'debit' : 'credit',
                        'amount'         => ($data->debit != 0) ? $data->debit : $data->credit,
                        'payment_method' => $payment_method,
                        'account_id'     => $account->chart_of_account_id,
                        'cheque_no'      => ($payment_method != 1) ? $account->description : ''
                    ];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('dealer-advance-delete')){
                $result   = $this->model->where('voucher_no',$request->id)->delete();
                $output   = $this->delete_message($result);
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
            if(permission('dealer-advance-bulk-delete')){
                $result   = $this->model->whereIn('voucher_no',$request->ids)->delete();
                $output   = $this->bulk_delete_message($result);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    
}


