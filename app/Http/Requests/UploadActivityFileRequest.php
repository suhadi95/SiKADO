<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadActivityFileRequest extends FormRequest
{
    public const ALLOWED_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
    ];

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
                // Validasi berdasarkan ekstensi (bukan MIME).
                // PPTX/DOCX/XLSX sering terdeteksi sebagai application/zip di server.
                File::default()
                    ->extensions(self::ALLOWED_EXTENSIONS)
                    ->max(51200),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => 'Pilih minimal satu file untuk diunggah.',
            'files.*.required' => 'Pilih minimal satu file untuk diunggah.',
            'files.*.uploaded' => 'File gagal diunggah. Pastikan ukuran maksimal 50 MB dan batas upload PHP di server mencukupi (upload_max_filesize / post_max_size).',
            'files.*.max' => 'Setiap file tidak boleh lebih dari 50 MB.',
            'files.*.extensions' => 'Format file harus PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, PPT, atau PPTX.',
        ];
    }
}
