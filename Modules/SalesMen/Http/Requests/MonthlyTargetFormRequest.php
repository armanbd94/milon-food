<?php

namespace Modules\SalesMen\Http\Requests;

use App\Http\Requests\FormRequest;

class MonthlyTargetFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        

        
        if(request()->type == 'store'){
            $this->rules['month']        = ['required', 'numeric'];
            $this->rules['warehouse_id'] = ['required'];
            if(request()->has('salesmen'))
            {
                foreach (request()->salesmen as $key => $value) {
                    $this->rules ['salesmen.'.$key.'.id']              = ['required'];
                    $this->rules ['salesmen.'.$key.'.target_value']    = ['required','numeric','gt:0'];
                    $this->rules ['salesmen.'.$key.'.commission_rate'] = ['required','numeric','gte:0'];
                    $this->messages['salesmen.'.$key.'.id.required']              = 'This field is required';
                    $this->messages['salesmen.'.$key.'.target_value.required']    = 'This field is required';
                    $this->messages['salesmen.'.$key.'.target_value.numeric']     = 'The value must be numeric';
                    $this->messages['salesmen.'.$key.'.target_value.gt']          = 'The value must be greater than 0';
                    $this->messages['salesmen.'.$key.'.commission_rate.required'] = 'This field is required';
                    $this->messages['salesmen.'.$key.'.commission_rate.numeric']  = 'The value must be numeric';
                    $this->messages['salesmen.'.$key.'.commission_rate.gte']      = 'The value must be greater than or equal 0';
                }
            }
        }else{
            
            $this->rules['target_value']    = ['required','numeric','gt:0'];
            $this->rules['commission_rate'] = ['required','numeric','gte:0'];
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
