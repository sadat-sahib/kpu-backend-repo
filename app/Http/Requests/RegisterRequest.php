<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;


class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "firstName" => "required|string|max:255",
            "lastName" => "required|string|max:255",

            'email' => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
            "password" => "required|string|min:8|max:64",
            "phone" => [
                'required',
                'string',
                'max:15',
            ],
            

            "nin" => [
                'required',
                'string',
                'max:20',
            ],
            "nic" => [
                'required',
                'string',
                'max:20',
            ],
            "original_residence" => "required|string|max:255",
            "current_residence" => "required|string|max:255",
            "fac_id" => "required|integer|exists:faculties,id",
            "dep_id" => "required|integer|exists:departments,id",
            "type" => "required|in:teacher,student",
            "image" => "nullable|image|max:1024|min:256",
            "status" => "nullable|string|in:active,inactive" // optional, used in controller logic
        ];
    }

    public function messages(): array
    {
        return [
            "firstName.required" => "نام ضروری می باشد",
            "firstName.string" => "نام باید شامل حروف باشد",
            "firstName.max" => "نام نمی تواند بیشتر از ۲۵۵ حرف باشد",



            "lastName.required" => "تخلص ضروری می باشد",
            "lastName.string" => "تخلص باید شامل حروف باشد",
            "lastName.max" => "تخلص نمی تواند بیشتر از ۲۵۵ حرف باشد",

            "email.required" => "ایمیل ضروری می باشد",
            "email.email" => "لطفاً یک ایمیل معتبر وارد کنید",
            "email.unique" => "این ایمیل قبلاً استفاده شده است",
            "email.max" => "ایمیل نمی تواند بیشتر از ۲۵۵ حرف باشد",

            "password.required" => "پسورد ضروری می باشد",
            "password.string" => "پسورد باید شامل حروف یا عدد باشد",
            "password.min" => "پسورد نمی تواند کمتر از ۸ حرف باشد",
            "password.max" => "پسورد نمی تواند بیشتر از ۶۴ حرف باشد",

            "phone.required" => "شماره تلفن ضروری می باشد",
            "phone.string" => "شماره تلفن باید معتبر باشد",
            "phone.max" => "شماره تلفن نمی تواند بیشتر از ۱۵ رقم باشد",


            "nin.required" => "نمبر تذکره ضروری می باشد",
            "nin.string" => "نمبر تذکره باید معتبر باشد",
            "nin.max" => "نمبر تذکره نمی تواند بیشتر از ۲۰ حرف باشد",

            "nic.required" => "ای دی کارت پوهنتون ضروری می باشد",
            "nic.string" => "ای دی کارت پوهنتون باید معتبر باشد",
            "nic.max" => "ای دی کارت نمی تواند بیشتر از ۲۰ حرف باشد",

            "original_residence.required" => "سکونت اصلی خود را وارد کنید",
            "original_residence.string" => "سکونت اصلی باید معتبر باشد",
            "original_residence.max" => "سکونت اصلی نمی تواند بیشتر از ۲۵۵ حرف باشد",

            "current_residence.required" => "سکونت فعلی خود را وارد کنید",
            "current_residence.string" => "سکونت فعلی باید معتبر باشد",
            "current_residence.max" => "سکونت فعلی نمی تواند بیشتر از ۲۵۵ حرف باشد",

            "fac_id.required" => "فاکولته ضروری می باشد",
            "fac_id.integer" => "فاکولته باید یک عدد معتبر باشد",
            "fac_id.exists" => "فاکولته انتخاب شده در سیستم موجود نیست",

            "dep_id.required" => "دیپارتمنت ضروری می باشد",
            "dep_id.integer" => "دیپارتمنت باید یک عدد معتبر باشد",
            "dep_id.exists" => "دیپارتمنت انتخاب شده در سیستم موجود نیست",

            "type.required" => "پوزیشن شما ضروری می باشد",
            "type.in" => "شما می توانید استاد یا محصل را انتخاب کنید",

            "image.image" => "لطفاً یک عکس معتبر وارد کنید",
            "image.max" => "حجم عکس نمی تواند بیشتر از ۱ مگابایت باشد",
            "image.min" => "حجم عکس نمی تواند کمتر از ۲۵۶ کیلوبایت باشد",

            "status.in" => "وضعیت باید یکی از active یا inactive باشد",
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! $validator->errors()->count()) {
                $secret = env('RECAPTCHA_SECRET_KEY');
                $response = $this->input('recaptcha_token');
                $remoteip = $this->ip();

                if (! $secret) {
                    $validator->errors()->add('recaptcha_token', 'خطا: کلید reCAPTCHA تنظیم نشده است.');
                    return;
                }

                try {
                        $verify = Http::timeout(10)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $secret,
                        'response' => $response,
                        'remoteip' => $remoteip,
                    ]);

                    if (! $verify->json('success')) {
                        $validator->errors()->add('recaptcha_token', 'تأیید reCAPTCHA ناموفق بود. لطفاً دوباره تلاش کنید.');
                    }
                } catch (\Exception $e) {
                    $validator->errors()->add('recaptcha_token', 'خطا در اتصال به سرویس reCAPTCHA.');
                }
            }
        });
    }
}
