<?php

namespace App\Http\Controllers;

use App\Http\Requests\UniqueValidationRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Validator;

class ValidationController extends Controller
{
    public function unique(UniqueValidationRequest $request)
    {
        $rule = Rule::unique($request->get('table'), $request->get('column'))
            ->whereNull('deleted_at')
            ->ignore($request->get('ignore'));

        return response($this->makeValidation($request, $rule)->fails() ? 0 : 1, 200);
    }

    private function makeValidation(Request $request, Unique $rule)
    {
        $data = ['value' => $request->get('value')];

        return Validator::make($data, ['value' => $rule]);
    }
}
