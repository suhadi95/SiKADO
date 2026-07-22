<?php

namespace App\Services;

use App\Enums\ActivityFileType;
use App\Models\Activity;
use App\Models\ActivityFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ActivityFileService
{
    public function __construct(
        protected GoogleDriveService $drive,
        protected FileNamingService $naming
    ) {}

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, ActivityFile>
     */
    public function upload(Activity $activity, ActivityFileType $type, array $files): array
    {
        if (! $activity->drive_folder_id || $activity->drive_sync_failed) {
            $this->ensureDriveFolder($activity);
            $activity->refresh();
        }

        if (! $activity->drive_folder_id || $activity->drive_sync_failed) {
            throw new RuntimeException('Folder Google Drive kegiatan bermasalah. Periksa pengaturan Google Drive.');
        }

        return DB::transaction(function () use ($activity, $type, $files) {
            $created = [];
            $existingNames = $activity->files()->pluck('stored_name')->all();

            foreach ($files as $file) {
                $storedName = $this->naming->storedFileName(
                    $activity,
                    $type,
                    $file->getClientOriginalName(),
                    $existingNames
                );

                try {
                    $uploaded = $this->drive->uploadFile(
                        $activity->drive_folder_id,
                        $file,
                        $storedName
                    );
                } catch (Throwable $e) {
                    throw new RuntimeException('Gagal mengunggah file ke Google Drive: '.$e->getMessage(), 0, $e);
                }

                $activityFile = ActivityFile::query()->create([
                    'activity_id' => $activity->id,
                    'type' => $type->value,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => $storedName,
                    'drive_file_id' => $uploaded['id'],
                    'mime_type' => $uploaded['mime_type'] ?? $file->getMimeType(),
                    'size' => $uploaded['size'] ?? $file->getSize(),
                ]);

                $existingNames[] = $storedName;
                $created[] = $activityFile;
            }

            return $created;
        });
    }

    protected function ensureDriveFolder(Activity $activity): void
    {
        try {
            if (! $this->drive->isConfigured()) {
                throw new RuntimeException('Google Drive belum dikonfigurasi.');
            }

            if ($activity->drive_folder_id) {
                $folder = $this->drive->renameFolder(
                    $activity->drive_folder_id,
                    $activity->drive_folder_name
                );
            } else {
                $folder = $this->drive->createFolder($activity->drive_folder_name);
            }

            $activity->update([
                'drive_folder_id' => $folder['id'],
                'drive_folder_url' => $folder['url'],
                'drive_sync_failed' => false,
            ]);
        } catch (Throwable) {
            $activity->update(['drive_sync_failed' => true]);
        }
    }
}
