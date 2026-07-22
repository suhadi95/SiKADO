<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activity_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'requires_evidence' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'files' => ['nullable', 'array'],
            'files.*' => [
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_evidence' => $this->boolean('requires_evidence'),
        ]);
    }

    public function messages(): array
    {
        return [
            'files.*.max' => 'Setiap file tidak boleh lebih dari 10 MB.',
            'files.*.mimes' => 'Format file harus PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, PPT, atau PPTX.',
        ];
    }
}
