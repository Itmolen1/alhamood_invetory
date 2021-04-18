<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
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
            'Amount.required' => 'Amount Cannot be empty.',
        ];
    }
}
