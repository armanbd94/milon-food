<?php

namespace Modules\StockReturn\Http\Requests;

use App\Http\Requests\FormRequest;

class PurchaseReturnRequest extends FormRequest
{
    protected $rules;
    protected $messages;

    public function rules()
    {
        $this->rules['memo_no']    = ['required'];
        $this->rules['purchase_date'] = ['required','date','date_format:Y-m-d'];
        $this->rules['return_date']   = ['required','date','date_format:Y-m-d'];
        $this->rules['supplier_name']   = ['required'];

        if(request()->has('materials'))
        {
            foreach (request()->materials as $key => $value) {
                if($value['return'] == 1)
                {
                    $this->rules['materials.'.$key.'.return_qty']     = ['required','numeric','gt:0','lte:'.$value['purchase_qty']];
                    $this->rules['materials.'.$key.'.deduction_rate'] = ['nullable','numeric','gt:0'];

                    $this->messages['materials.'.$key.'.return_qty.required']    = 'This field is required';
                    $this->messages['materials.'.$key.'.return_qty.numeric']     = 'The value must be numeric';
                    $this->messages['materials.'.$key.'.return_qty.gt']          = 'The value must be greater than 0';
                    $this->messages['materials.'.$key.'.deduction_rate.numeric'] = 'The value must be numeric';
                    $this->messages['materials.'.$key.'.deduction_rate.gt']      = 'The value must be greater than 0';
                }
            }
        }
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
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
