<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => "required|unique:departments,name,{$this->id},id", // Fixes the 'except,id' issue
            "fac_id" => "required|exists:faculties,id"
        ];
    }

    public function messages(): array
    {
        return [
            "fac_id.required" => "فاکولته معتبر نمی‌باشد",
            "fac_id.exists" => "فاکولته‌ای با این مشخصات وجود ندارد",
            "name.required" => "نام ضروری است",
            "name.unique" => "این نام قبلاً انتخاب شده است",

        ];
    }
}
