<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'registrationNumber' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'registrationNumber.unique' => 'Already Exist.',
            'registrationNumber.required' => 'Name Cannot be empty.',
        ];
    }
}
