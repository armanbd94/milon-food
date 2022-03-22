<?php

namespace Modules\DamageProduct\Http\Requests;

use App\Http\Requests\FormRequest;

class DamageProductRequest extends FormRequest
{
    protected $rules;
    protected $messages;

    public function rules()
    {
        $this->rules['memo_no']  = ['required'];
        $this->rules['sale_date']   = ['required','date','date_format:Y-m-d'];
        $this->rules['damage_date'] = ['required','date','date_format:Y-m-d'];
        $this->rules['customer_name'] = ['required'];

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {
                $this->rules['products.'.$key.'.damage_qty']     = ['required','numeric','gt:0','lte:'.$value['sold_qty']];

                $this->messages['products.'.$key.'.damage_qty.required']    = 'The damage quantity field is required';
                $this->messages['products.'.$key.'.damage_qty.numeric']     = 'The damage quantity value must be numeric';
                $this->messages['products.'.$key.'.damage_qty.gt']          = 'The damage quantity value must be greater than 0';
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
