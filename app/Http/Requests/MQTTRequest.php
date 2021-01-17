<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MQTTRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'mac_address' => 'required',
        ];
    }
}
