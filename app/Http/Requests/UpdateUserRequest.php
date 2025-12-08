<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "firstName" => "required|min:5|max:20",
            "lastName" => "required|min:5|max:20",
            "password" => "required",
            "phone" => "required",
            "nin" => "required",
            "nic" => "required",
            "original_residence" => "required",
            "current_residence" => "required",
            "fac_id" => "required",
            "dep_id" => "required",
            "type" => "required|in:teacher,student",
        ];

        if ($this->has('email')) {
            $rules['email'] = [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('id')),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'firstName.required' => 'وارد کردن نام الزامی است.',
            'firstName.min' => 'نام باید حداقل ۵ کاراکتر باشد.',
            'firstName.max' => 'نام نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',

            'lastName.required' => 'وارد کردن نام خانوادگی الزامی است.',
            'lastName.min' => 'نام خانوادگی باید حداقل ۵ کاراکتر باشد.',
            'lastName.max' => 'نام خانوادگی نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',

            'email.required' => 'وارد کردن ایمیل الزامی است.',
            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.unique' => 'ایمیل وارد شده قبلاً ثبت شده است.',

            'password.required' => 'وارد کردن رمز عبور الزامی است.',

            'phone.required' => 'وارد کردن شماره تلفن الزامی است.',

            'nin.required' => 'وارد کردن ای دی تذکره الزامی است.',

            'nic.required' => 'وارد کردن ای دی پوهنتون  الزامی است.',

            'original_residence.required' => 'وارد کردن محل سکونت اصلی الزامی است.',

            'current_residence.required' => 'وارد کردن محل سکونت فعلی الزامی است.',

            'fac_id.required' => 'وارد کردن شناسه فاکولته الزامی است.',

            'dep_id.required' => 'وارد کردن شناسه دیپارتمنت الزامی است.',

            'status.required' => 'وارد کردن وضعیت الزامی است.',

            'type.required' => 'وارد کردن نوع کاربر الزامی است.',
            'type.in' => 'نوع کاربر باید یکی از مقادیر معلم یا دانشجو باشد.',
        ];
    }
}
