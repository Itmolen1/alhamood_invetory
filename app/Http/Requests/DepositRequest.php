<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "Amount" => 'required'
        ];
    }

    public function messages()
    {
        return [
            'Amount.unique' => 'Already Exist.',
            'Amount.required' => 'Name Cannot be empty.',
        ];
    }
}
