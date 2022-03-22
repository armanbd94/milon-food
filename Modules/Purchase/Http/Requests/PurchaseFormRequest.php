<?php

namespace Modules\Purchase\Http\Requests;

use App\Http\Requests\FormRequest;

class PurchaseFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['memo_no']         = ['required','unique:purchases,memo_no'];
        $this->rules['purchase_date']   = ['required','date_format:Y-m-d'];
        $this->rules['supplier_id']     = ['required'];
        $this->rules['purchase_status'] = ['required'];
        $this->rules['order_discount']  = ['nullable','numeric','gte:0'];
        $this->rules['shipping_cost']   = ['nullable','numeric','gte:0'];
        $this->rules['labor_cost']   = ['nullable','numeric','gte:0'];
        if(request()->has('purchase_id'))
        {
            $this->rules['memo_no'][1] = 'unique:purchases,memo_no,'.request()->purchase_id;
        }

        if(request()->has('materials'))
        {
            foreach (request()->materials as $key => $value) {
                $this->rules   ['materials.'.$key.'.qty']          = ['required','numeric','gt:0'];
                $this->messages['materials.'.$key.'.qty.required'] = 'This field is required';
                $this->messages['materials.'.$key.'.qty.numeric']  = 'The value must be numeric';
                $this->messages['materials.'.$key.'.qty.gt']       = 'The value must be greater than 0';
            }
        }

        if(!request()->has('purchase_id'))
        {
            $this->rules['payment_status'] = ['required'];
            if(!empty(request()->payment_status) && request()->payment_status != 3)
            {
                $this->rules['paid_amount'] = ['required','numeric','gte:0','lte:'.request()->grand_total];
                if(request()->payment_status == 1)
                {
                    $this->rules['paid_amount'][2] = 'min:'.request()->grand_total;
                    $this->rules['paid_amount'][3] = 'max:'.request()->grand_total;
                }elseif (request()->payment_status == 2) {
                    $this->rules['paid_amount'][3] = 'lt:'.request()->grand_total;
                }
                $this->rules['payment_method'] = ['required'];
                $this->rules['account_id'] = ['required'];
                if(!empty(request()->payment_method) && request()->payment_method == 2){
                    $this->rules['cheque_number'] = ['required'];
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
