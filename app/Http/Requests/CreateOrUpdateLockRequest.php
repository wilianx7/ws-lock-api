<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrUpdateLockRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'lock_data' => 'required|array',
            'lock_data.id' => 'sometimes|nullable',
            'lock_data.name' => 'required',
            'lock_data.mac_address' => 'required_without: lock_data.id',
            'lock_data.users' => 'sometimes|nullable|array',
            'lock_data.users.*.id' => 'required',
        ];
    }
}
