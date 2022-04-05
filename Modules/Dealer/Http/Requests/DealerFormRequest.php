<?php

namespace Modules\Dealer\Http\Requests;

use App\Http\Requests\FormRequest;

class DealerFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    public function rules()
    {
        $this->rules['name']                  = ['required', 'string'];
        $this->rules['shop_name']             = ['nullable', 'string'];
        $this->rules['username']              = ['required', 'string', 'max:30','unique:dealers,username'];
        $this->rules['mobile']                = ['required', 'string', 'max:15', 'unique:dealers,mobile'];
        $this->rules['email']                 = ['nullable', 'string', 'email', 'unique:dealers,email'];
        $this->rules['password']              = ['required', 'string', 'min:8', 'confirmed'];
        $this->rules['password_confirmation'] = ['required', 'string', 'min:8'];
        $this->rules['warehouse_id']           = ['required'];
        $this->rules['district_id']           = ['required'];
        $this->rules['upazila_id']            = ['required'];
        $this->rules['address']               = ['required'];
        $this->rules['avatar']                = ['nullable','mimes:png,jpg,jpeg,webp,svg','max:2048'];
        $this->rules['previous_balance']      = ['nullable','numeric','gt:0'];
        if(request()->dealer_id){
            $this->rules['username'][3]              = 'unique:dealers,username,'.request()->dealer_id;
            $this->rules['mobile'][3]                = 'unique:dealers,mobile,'.request()->dealer_id;
            $this->rules['email'][3]                 = 'unique:dealers,email,'.request()->dealer_id;
            $this->rules['password'][0]              = 'nullable';
            $this->rules['password_confirmation'][0] = 'nullable';
        }
        $collection = collect(request());
        if($collection->has('products')){
            foreach (request()->products as $key => $value) {
                $this->rules   ['products.'.$key.'.id']              = ['required','integer'];
                $this->rules   ['products.'.$key.'.commission_rate'] = ['required','numeric','gte:0'];

                $this->messages['products.'.$key.'.id.required']              = 'The product field is required';
                $this->messages['products.'.$key.'.id.integer']               = 'The product field value must be integer';
                $this->messages['products.'.$key.'.commission_rate.required'] = 'This field is required';
                $this->messages['products.'.$key.'.commission_rate.integer']  = 'This field value must be numeric';
                $this->messages['products.'.$key.'.commission_rate.gte']      = 'This field value must be greater than or equal to 0';
            }
        }
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
    }


    public function authorize()
    {
        return true;
    }
}
