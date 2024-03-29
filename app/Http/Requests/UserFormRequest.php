<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;

class UserFormRequest extends FormRequest
{
    protected $rules = [];
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['name']                  = ['required', 'string'];
        $this->rules['username']              = ['required', 'string', 'max:30','unique:users,username'];
        $this->rules['phone']                 = ['required', 'string', 'max:15', 'unique:users,phone'];
        $this->rules['email']                 = ['nullable', 'string', 'email', 'unique:users,email'];
        $this->rules['gender']                = ['required'];
        $this->rules['role_id']               = ['required', 'integer'];
        $this->rules['password']              = ['required', 'string', 'min:8', 'confirmed'];
        $this->rules['password_confirmation'] = ['required', 'string', 'min:8'];
        $this->rules['parent_id']             = ['required'];
        $this->rules['avatar']                = ['nullable','mimes:png,jpg,jpeg,webp,svg','max:2048'];
        if(request()->update_id){
            $this->rules['username'][3]              = 'unique:users,username,'.request()->update_id;
            $this->rules['phone'][3]                 = 'unique:users,phone,'.request()->update_id;
            $this->rules['email'][3]                 = 'unique:users,email,'.request()->update_id;
            $this->rules['password'][0]              = 'nullable';
            $this->rules['password_confirmation'][0] = 'nullable';
        }
        if(!empty(request()->role_id) && request()->role_id != 2)
        {
            $this->rules['warehouse_id']                = ['required'];
        }

        return $this->rules;
    }

    public function messages()
    {
        return [
            'parent_id.required' => 'The control by field is required'
        ];
    }
}
