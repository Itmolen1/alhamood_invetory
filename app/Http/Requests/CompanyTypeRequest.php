<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'Name' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'Name.unique' => 'Already Exist.',
            'Name.required' => 'Name Cannot be empty.',
        ];
    }
}
