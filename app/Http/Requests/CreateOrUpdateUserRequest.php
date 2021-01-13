<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrUpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_data' => 'required|array',
            'user_data.id' => 'sometimes|nullable',
            'user_data.name' => 'required',
            'user_data.email' => 'required',
            'user_data.login' => 'required',
            'user_data.password' => 'required_without:user_data.id',
        ];
    }
}
