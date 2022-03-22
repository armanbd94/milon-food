<?php

namespace Modules\Production\Http\Requests;

use App\Http\Requests\FormRequest;

class OperationRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        $collection = collect(request());
        if($collection->has('production')){
            
            foreach (request()->production as $key => $value) {
                
                $this->rules['production.'.$key.'.fg_qty']         = ['required','numeric','gt:0'];
                $this->rules['production.'.$key.'.materials_per_unit_cost']  = ['required','numeric','gt:0'];

                $this->messages['production.'.$key.'.fg_qty.required']        = 'This field is required';
                $this->messages['production.'.$key.'.fg_qty.numeric']         = 'This field value must be numeric';
                $this->messages['production.'.$key.'.fg_qty.gt']              = 'This field value must be greater than 0 ';
                $this->messages['production.'.$key.'.materials_per_unit_cost.required'] = 'This field is required';
                $this->messages['production.'.$key.'.materials_per_unit_cost.numeric']  = 'This field value must be numeric';
                $this->messages['production.'.$key.'.materials_per_unit_cost.gt']       = 'This field value must be greater than 0 ';
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
