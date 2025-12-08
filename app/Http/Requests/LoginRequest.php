<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'recaptcha_token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'ایمیل ضروری می باشد',
            'email.email' => 'ایمیل معتبر وارد کنید',
            'password.required' => 'پسورد ضروری می باشد',
            'recaptcha_token.required' => 'لطفاً تأیید کنید که ربات نیستید.',
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