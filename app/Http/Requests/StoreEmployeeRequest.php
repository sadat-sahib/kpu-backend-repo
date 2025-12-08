<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'name' => 'required|unique:employees,name',
            'email' => 'required|email|unique:employees,email',
            'password' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام ضروری است.',
            'name.unique' => 'این نام قبلاً ثبت شده است.',
            'email.required' => 'وارد کردن ایمیل ضروری است.',
            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'password.required' => 'وارد کردن رمز عبور ضروری است.'
        ];
    }
}
