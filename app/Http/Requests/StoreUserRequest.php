<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            "firstName" => "required|min:5|max:20",
            "lastName" => "required|min:5|max:20",
            "email" => "required|email|unique:users",
            "password" => "required",
            "phone" => "required",
            "nin" => "required",
            "nic" => "required",
            "original_residence" => "required",
            "current_residence" => "required",
            "fac_id" => "required",
            "dep_id" => "required",
            "status" => "required",
            "type" => "required|in:teacher,student",
            "image" => "required|image|max:1024|min:256"
        ];
    }

    public function messages(): array
    {
        return [
            "firstName.required" => "نام ضروری می باشد",
            "lastName.required" => "تخلص ضروری می با شد",
            "email.required" => "ایمیل ضروری می باشد",
            "password.required" => "پسورد ضروری می باشد",
            "phone.required" => "شماره تلفن ضروری می باشد",
            "nin.required" => "نمبر تذکره ضروری می باشد",
            "nic.required" => "ای دی کارت پوهنتون ضروری می با شد",
            "original_residence.required" => "سکونت اصلی خود را وارد کنید",
            "current_residence.required" => "سکونت فعلی خود را وارد کنید",
            "fac_id.required" => "فاکولته ضروری می باشد",
            "dep_id.required" => "دیپاتمنت ضروری می باشد",
            "type.required" => "پوزیشن شما ضروری می باشد",
            "type.in" => "شما می توانید استاد یا محصل را انتخاب کنید",
            "image.required" => "عکس ضروری می باشد",
            "image.image" => "لطفا یک عکس معتبر وارد کنید",
            "image.max" => "حجم عکس زیاد می باشد",
            "image.min" => "حجم عکس کم می باشد",
            "status.required" => "فیلد وضعیت ضروری می باشد"
        ];
    }
}
