<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SectionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            "section" => "required|unique:sections,section,{$this->id},id",
        ];
    }

    public function messages(): array
    {
        return [
            "section.required" => "نام الماری ضروری می‌باشد.",
            "section.unique" => "این نام قبلاً انتخاب شده است.",
        ];
    }
}
