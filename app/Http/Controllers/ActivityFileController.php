<?php

namespace App\Http\Controllers;

use App\Enums\ActivityFileType;
use App\Http\Requests\UploadActivityFileRequest;
use App\Models\Activity;
use App\Services\ActivityFileService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ActivityFileController extends Controller
{
    public function __construct(
        protected ActivityFileService $files
    ) {}

    public function uploadBasic(UploadActivityFileRequest $request, Activity $activity): RedirectResponse
    {
        return $this->upload($request, $activity, ActivityFileType::Basic);
    }

    public function uploadEvidence(UploadActivityFileRequest $request, Activity $activity): RedirectResponse
    {
        return $this->upload($request, $activity, ActivityFileType::Evidence);
    }

    protected function upload(
        UploadActivityFileRequest $request,
        Activity $activity,
        ActivityFileType $type
    ): RedirectResponse {
        try {
            $uploaded = $this->files->upload(
                $activity,
                $type,
                $request->file('files', [])
            );

            $count = count($uploaded);
            $label = $type->label();

            return back()->with(
                'success',
                "{$count} file {$label} berhasil diunggah ke Google Drive."
            );
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
