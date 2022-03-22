<?php

namespace Modules\SalesMen\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SalesMen\Entities\Salesmen;
use App\Http\Controllers\BaseController;
use Modules\SalesMen\Entities\SalesmenLedger;

class SalesmenLedgerController extends BaseController
{
    public function __construct(SalesmenLedger $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('salesmen-ledger-access')){
            $this->setPageData('Salesmen Ledger','Salesmen Ledger','fas fa-file-invoice-dollar',[['name'=>'Salesmen','link'=>route('sales.representative')],['name'=>'Salesmen Ledger']]);
            $salesmen = Salesmen::with('coa')->where(['status'=>1])->orderBy('name','asc')->get();
            return view('salesmen::ledger.index',compact('salesmen'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('salesmen-ledger-access')){

                if (!empty($request->salesmen_id)) {
                    $this->model->setSalesmenID($request->salesmen_id);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
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
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
