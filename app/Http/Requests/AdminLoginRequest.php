<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;

class AdminLoginRequest extends FormRequest
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
            "email" => "required|email",
            "password" => "required|string",
            // 'type'     => ['required', 'in:employee,assistant'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'ایمیل ضروری می باشد',
            'email.email' => 'لطفا ایمیل معتبر وارد کنید',
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
