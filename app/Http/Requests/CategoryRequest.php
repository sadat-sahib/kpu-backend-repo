<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
            "name" => "required|unique:categories,name,{$this->id},id"
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "نام کتگوری ضروری می‌باشد.",
            "name.unique" => "این نام قبلاً انتخاب شده است."
        ];
    }
}
