<?php
namespace Modules\Transfer\Http\Requests;

use App\Http\Requests\FormRequest;


class TransferFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;

    public function rules()
    {
        $this->rules['challan_no']       = ['required','unique:transfers,challan_no'];
        $this->rules['transfer_date']     = ['required','date_format:Y-m-d'];
        $this->rules['transfer_id']      = ['nullable'];
        $this->rules['from_warehouse_id'] = ['required'];
        $this->rules['to_warehouse_id']   = ['required'];
        $this->rules['item']              = ['numeric','gte:0'];
        $this->rules['total_qty']         = ['numeric','gte:0'];
        $this->rules['grand_total']       = ['numeric','gte:0'];
        $this->rules['shipping_cost']     = ['nullable','numeric','gte:0'];
        $this->rules['driver_name']       = ['nullable','string'];
        $this->rules['dmobile_no']       = ['nullable','string'];
        $this->rules['transfer_status']   = ['required'];
        $this->rules['receiver_id']       = ['required'];
        $this->rules['note']              = ['nullable'];

        $this->messages['from_warehouse_id.required'] = 'The from warehouse field is required';
        $this->messages['to_warehouse_id.required']   = 'The to warehouse field is required';
        $this->messages['receiver_id.required']       = 'The receiver field is required';

        if(request()->has('transfer_id'))
        {
            $this->rules['challan_no'][1] = 'unique:transfers,challan_no,'.request()->transfer_id;
        }

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {
                $this->rules   ['products.'.$key.'.transfer_qty']          = ['required','numeric','gt:0','lte:'.$value['stock_qty']];
                $this->messages['products.'.$key.'.transfer_qty.required'] = 'This field is required';
                $this->messages['products.'.$key.'.transfer_qty.numeric']  = 'The value must be numeric';
                $this->messages['products.'.$key.'.transfer_qty.gt']       = 'The value must be greater than 0';
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
