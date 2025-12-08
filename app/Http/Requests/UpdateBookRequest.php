<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = is_object($this->route('book'))
            ? $this->route('book')->id
            : $this->route('book'); // Handles both model and ID

        $rules = [
            'title'           => 'required|string|max:255|unique:books,title,' . $bookId,
            'author'          => 'required|string|max:255',
            'publisher'       => 'nullable|string|max:255',
            'publicationYear' => 'required|integer|min:1000|max:' . date('Y'),
            'lang'            => 'required|in:en,fa,pa',
            'edition'         => 'nullable|string|max:255',
            'translator'      => 'nullable|string|max:255',
            'isbn'            => 'nullable|string|max:20|unique:books,isbn,' . $bookId,
            'code'            => 'required|string|max:50|unique:books,code,' . $bookId,
            'description'     => 'nullable|string',
            'cat_id'          => 'required|exists:categories,id',
            'dep_id'          => 'required|exists:departments,id',
            'format'          => 'required|in:hard,pdf,both',
            'borrow'          => 'required|in:yes,no',
            'image'           => 'nullable|image|mimes:png,jpg,jpeg|max:1024',
            'pdf'             => 'nullable|mimes:pdf|max:2048',
        ];

        if ($this->input('format') === 'pdf') {
            $rules = array_merge($rules, [
                'borrow' => 'prohibited',
                'sec_id' => 'prohibited',
                'shelf'  => 'prohibited',
                'total'  => 'prohibited',
                'code'   => 'prohibited',
            ]);
        } else {
            $rules = array_merge($rules, [
                'sec_id' => 'required|exists:sections,id',
                'shelf'  => 'required|string|max:50',
                'total'  => 'required|integer|min:1',
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required'         => 'وارد کردن عنوان کتاب ضروری می‌باشد.',
            'title.unique'           => 'این عنوان کتاب قبلاً ثبت شده است.',
            'title.max'              => 'عنوان کتاب نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'author.required'        => 'وارد کردن نام نویسنده کتاب ضروری می‌باشد.',
            'publisher.max'          => 'نام منتشرکننده نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'publicationYear.required' => 'وارد کردن سال انتشار ضروری می‌باشد.',
            'publicationYear.integer'  => 'سال انتشار باید یک عدد معتبر باشد.',
            'publicationYear.min'      => 'سال انتشار نمی‌تواند کمتر از 1000 باشد.',
            'publicationYear.max'      => 'سال انتشار نمی‌تواند بزرگتر از سال جاری باشد.',
            'lang.required'          => 'انتخاب زبان ضروری می‌باشد.',
            'lang.in'                => 'زبان انتخابی معتبر نمی‌باشد.',
            'edition.max'            => 'نسخه چاپ نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'isbn.max'               => 'شماره ISBN نمی‌تواند بیشتر از 20 کاراکتر باشد.',
            'isbn.unique'            => 'این شماره ISBN قبلاً ثبت شده است.',
            'code.required'          => 'وارد کردن کد کتاب ضروری می‌باشد.',
            'code.max'               => 'کد کتاب نمی‌تواند بیشتر از 50 کاراکتر باشد.',
            'code.unique'            => 'این کد کتاب قبلاً ثبت شده است.',
            'cat_id.required'        => 'انتخاب کتگوری ضروری می‌باشد.',
            'cat_id.exists'          => 'کتگوری انتخاب شده معتبر نمی‌باشد.',
            'dep_id.required'        => 'انتخاب دیپارتمنت ضروری می‌باشد.',
            'dep_id.exists'          => 'دیپارتمنت انتخاب شده معتبر نمی‌باشد.',
            'sec_id.required'        => 'انتخاب الماری ضروری می‌باشد.',
            'sec_id.exists'          => 'الماری انتخاب شده معتبر نمی‌باشد.',
            'format.required'        => 'انتخاب فرمت کتاب ضروری می‌باشد.',
            'format.in'              => 'فرمت انتخابی معتبر نمی‌باشد.',
            'borrow.required'        => 'تعیین قابلیت قرض گرفتن ضروری می‌باشد.',
            'borrow.in'              => 'مقدار قرض گرفتن باید "بله" یا "نه" باشد.',
            'image.image'            => 'عکس آپلود شده باید به فرمت تصویر باشد.',
            'image.mimes'            => 'عکس باید به فرمت‌های png، jpg یا jpeg باشد.',
            'image.max'              => 'اندازه عکس نباید بیشتر از 1024 کیلوبایت باشد.',
            'pdf.mimes'              => 'فایل PDF باید تنها به فرمت pdf باشد.',
            'pdf.max'                => 'اندازه فایل PDF نباید بیشتر از 2048 کیلوبایت باشد.',
        ];
    }
}
