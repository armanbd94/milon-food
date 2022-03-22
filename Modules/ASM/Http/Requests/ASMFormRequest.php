<?php

namespace Modules\ASM\Http\Requests;

use App\Http\Requests\FormRequest;

class ASMFormRequest extends FormRequest
{
    protected $rules = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['name']                  = ['required', 'string'];
        // $this->rules['username']              = ['required', 'string', 'max:30','unique:asms,username'];
        $this->rules['phone']                 = ['required', 'string', 'max:15', 'unique:asms,phone'];
        $this->rules['email']                 = ['nullable', 'string', 'email', 'unique:asms,email'];
        // $this->rules['password']              = ['required', 'string', 'min:8', 'confirmed'];
        // $this->rules['password_confirmation'] = ['required', 'string', 'min:8'];
        $this->rules['avatar']                = ['nullable','image', 'mimes:png,jpg,jpeg'];
        $this->rules['address']               = ['nullable', 'string'];
        $this->rules['nid_no']                = ['nullable'];
        $this->rules['monthly_target_value']  = ['nullable','numeric','gt:0'];
        $this->rules['district_id']           = ['required'];
        if(request()->update_id){
            // $this->rules['username'][3]              = 'unique:asms,username,'.request()->update_id;
            $this->rules['phone'][3]                 = 'unique:asms,phone,'.request()->update_id;
            $this->rules['email'][3]                 = 'unique:asms,email,'.request()->update_id;
            // $this->rules['password'][0]              = 'nullable';
            // $this->rules['password_confirmation'][0] = 'nullable';
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
