<?php

namespace Modules\Dealer\Http\Requests;

use App\Http\Requests\FormRequest;

class DealerAdvanceFormRequest extends FormRequest
{
    protected $rules = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['warehouse_id']   = ['required'];
        $this->rules['dealer']       = ['required'];
        $this->rules['type']           = ['required'];
        $this->rules['amount']         = ['required','numeric','gt:0'];
        $this->rules['payment_method'] = ['required'];
        $this->rules['account_id']     = ['required'];
        if(request()->payment_method == 2){
            $this->rules['cheque_number'] = ['required'];
        }
        return $this->rules;
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
