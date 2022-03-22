<?php

namespace Modules\Production\Http\Requests;

use App\Http\Requests\FormRequest;

class ProductionRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        $this->rules['batch_no'] = ['required','unique:productions,batch_no'];
        if(request()->update_id)
        {
            $this->rules['batch_no'][1] = 'unique:productions,batch_no,'.request()->update_id;
        }
        $this->rules['start_date']   = ['required','date','date_format:Y-m-d'];
        $this->rules['warehouse_id'] = ['required'];

        $collection = collect(request());
        if($collection->has('production')){
            
            foreach (request()->production as $key => $value) {

                $this->rules['production.'.$key.'.product_id'] = ['required'];
                $this->rules['production.'.$key.'.year']       = ['required'];
                $this->rules['production.'.$key.'.mfg_date']   = ['required','date','date_format:Y-m-d'];
                $this->rules['production.'.$key.'.exp_date']   = ['required','date','date_format:Y-m-d','after_or_equal:production.'.$key.'.mfg_date'];

                $this->messages['production.'.$key.'.product_id.required']     = 'This product name field is required';
                $this->messages['production.'.$key.'.year.required']           = 'This year field is required';
                $this->messages['production.'.$key.'.mfg_date.required']       = 'This mfg date field is required';
                $this->messages['production.'.$key.'.mfg_date.date']           = 'This mfg date value must be date';
                $this->messages['production.'.$key.'.mfg_date.date_format']    = 'This mfg date value format must be '.date('Y-m-d');
                $this->messages['production.'.$key.'.end_date.required']       = 'This end date field is required';
                $this->messages['production.'.$key.'.end_date.date']           = 'This end date value must be date';
                $this->messages['production.'.$key.'.end_date.date_format']    = 'This end date format must be '.date('Y-m-d');
                $this->messages['production.'.$key.'.end_date.after_or_equal'] = 'This end date must be equal or greater than mfg date';


                if(!empty($value['materials']) && count($value['materials']) > 0)
                {
                    foreach ($value['materials'] as $index => $item) {
                        $this->rules['production.'.$key.'.materials.'.$index.'.qty'] = ['required','numeric','gt:0'];

                        $this->messages['production.'.$key.'.materials.'.$index.'.qty.required']        = 'This quantity field is required';
                        $this->messages['production.'.$key.'.materials.'.$index.'.qty.numeric']         = 'This quantity field value must be numeric';
                        $this->messages['production.'.$key.'.materials.'.$index.'.qty.gt']              = 'This quantity field value must be greater than 0 ';
                    }
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
