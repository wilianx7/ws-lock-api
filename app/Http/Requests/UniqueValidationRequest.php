<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UniqueValidationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'table' => 'required',
            'column' => 'required',
            'ignore' => 'required',
            'value' => 'required',
            'extra_fields' => 'sometimes|nullable',
        ];
    }
}
