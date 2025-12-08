<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacultyRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => [
                'required',
                Rule::unique('faculties', 'name')->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام فاکولته اجباری است.',
            'name.unique'   => 'این نام فاکولته قبلاً ثبت شده است.',
        ];
    }
}
