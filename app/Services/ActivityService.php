<?php

namespace App\Services;

use App\Enums\ActivityFileType;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Throwable;

class ActivityService
{
    public function __construct(
        protected GoogleDriveService $drive,
        protected FileNamingService $naming
    ) {}

    public function create(array $data, array $basicFiles = []): Activity
    {
        return DB::transaction(function () use ($data, $basicFiles) {
            $activity = Activity::query()->create([
                'category_id' => $data['category_id'],
                'activity_date' => $data['activity_date'],
                'name' => $data['name'],
                'requires_evidence' => (bool) ($data['requires_evidence'] ?? true),
                'notes' => $data['notes'] ?? null,
                'drive_sync_failed' => false,
            ]);

            $this->syncDriveFolder($activity);

            if ($basicFiles !== [] && $activity->drive_folder_id && ! $activity->drive_sync_failed) {
                app(ActivityFileService::class)->upload(
                    $activity,
                    ActivityFileType::Basic,
                    $basicFiles
                );
            }

            return $activity->fresh(['category', 'files']);
        });
    }

    public function update(Activity $activity, array $data): Activity
    {
        return DB::transaction(function () use ($activity, $data) {
            $oldFolderName = $activity->drive_folder_name;

            $activity->fill([
                'category_id' => $data['category_id'],
                'activity_date' => $data['activity_date'],
                'name' => $data['name'],
                'requires_evidence' => (bool) ($data['requires_evidence'] ?? true),
                'notes' => $data['notes'] ?? null,
            ]);

            $activity->save();

            $newFolderName = $activity->drive_folder_name;

            if ($activity->drive_folder_id && $oldFolderName !== $newFolderName) {
                try {
                    $folder = $this->drive->renameFolder($activity->drive_folder_id, $newFolderName);
                    $activity->update([
                        'drive_folder_url' => $folder['url'],
                        'drive_sync_failed' => false,
                    ]);
                } catch (Throwable) {
                    $activity->update(['drive_sync_failed' => true]);
                }
            } elseif (! $activity->drive_folder_id || $activity->drive_sync_failed) {
                $this->syncDriveFolder($activity);
            }

            return $activity->fresh(['category', 'files']);
        });
    }

    public function delete(Activity $activity): void
    {
        DB::transaction(function () use ($activity) {
            $activity->load('files');

            foreach ($activity->files as $file) {
                if ($file->drive_file_id) {
                    try {
                        $this->drive->deleteFile($file->drive_file_id);
                    } catch (Throwable) {
                        // Best-effort Drive cleanup.
                    }
                }

                $file->delete();
            }

            if ($activity->drive_folder_id) {
                try {
                    $this->drive->deleteFolder($activity->drive_folder_id);
                } catch (Throwable) {
                    // Best-effort Drive cleanup.
                }
            }

            $activity->delete();
        });
    }

    public function syncDriveFolder(Activity $activity): Activity
    {
        try {
            if (! $this->drive->isConfigured()) {
                throw new \RuntimeException('Google Drive belum dikonfigurasi.');
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
            $activity->update([
                'drive_sync_failed' => true,
            ]);
        }

        return $activity->fresh();
    }
}
