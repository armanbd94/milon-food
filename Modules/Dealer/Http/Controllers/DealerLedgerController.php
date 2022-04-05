<?php

namespace Modules\Dealer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Dealer\Entities\DealerLedger;

class DealerLedgerController extends BaseController
{
    public function __construct(DealerLedger $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('dealer-ledger-access')){
            $this->setPageData('Dealer Ledger','Dealer Ledger','fas fa-file-invoice-dollar',[['name'=>'Dealer','link'=>route('dealer')],['name'=>'Dealer Ledger']]);
            $dealers = DB::table('dealers')->where('status', 1)->get();
            return view('dealer::ledger.index',compact('dealers'));
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
            $debit = $credit = $balance = 0;
            foreach ($list as $value) {
                $debit += $value->debit;
                $credit += $value->credit;
                $balance = $debit - $credit;
                $row = [];
                $row[] = $value->voucher_date;
                $row[] = $value->description;
                $row[] = $value->voucher_no;
                $row[] = $value->debit ? number_format($value->debit,2, '.', ',') :  number_format(0,2, '.', ',');
                $row[] = $value->credit ? number_format($value->credit,2, '.', ',') :  number_format(0,2, '.', ',');
                $row[] = number_format(($balance),2, '.', ',');
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
