<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadActivityFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])
                    ->max(10240),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => 'Pilih minimal satu file untuk diunggah.',
            'files.*.max' => 'Setiap file tidak boleh lebih dari 10 MB.',
            'files.*.mimes' => 'Format file harus PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, PPT, atau PPTX.',
            'files.*.extensions' => 'Format file harus PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, PPT, atau PPTX.',
        ];
    }
}
