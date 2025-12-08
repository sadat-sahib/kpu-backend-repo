<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
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
        $userId = $this->route('id'); // Assuming the route contains the user ID being updated.

        return [
            "name" => "required|unique:employees,name," . $userId,
            "email" => "required|email|unique:employees,email," . $userId,
            "password" => "required|min:6"
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "وارد کردن نام ضروری است.",
            "name.unique" => "این نام قبلاً ثبت شده است.",
            "email.required" => "وارد کردن ایمیل ضروری است.",
            "email.email" => "ایمیل وارد شده معتبر نیست.",
            "email.unique" => "این ایمیل قبلاً ثبت شده است.",
            "password.required" => "وارد کردن رمز عبور ضروری است.",
            "password.min" => "رمز عبور باید حداقل ۶ کاراکتر باشد."
        ];
    }
}
