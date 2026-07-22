<?php

namespace App\Services;

use App\Enums\ActivityFileType;
use App\Models\Activity;
use Illuminate\Support\Carbon;

class FileNamingService
{
    public function folderName(Activity|Carbon $dateOrActivity, ?string $name = null): string
    {
        if ($dateOrActivity instanceof Activity) {
            return $dateOrActivity->drive_folder_name;
        }

        return $dateOrActivity->format('y-m-d').' '.$name;
    }

    public function storedFileName(
        Activity $activity,
        ActivityFileType $type,
        string $originalName,
        array $existingStoredNames = []
    ): string {
        $prefix = match ($type) {
            ActivityFileType::Evidence => 'Bukti',
            ActivityFileType::Basic => 'Dasar',
        };

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseOriginal = pathinfo($originalName, PATHINFO_FILENAME);
        $safeOriginal = $this->sanitizeName($baseOriginal);
        $datePart = $activity->activity_date->format('y-m-d');
        $activityName = $this->sanitizeName($activity->name);

        $base = sprintf(
            '%s %s %s_%s',
            $prefix,
            $datePart,
            $activityName,
            $safeOriginal
        );

        $candidate = $extension !== ''
            ? $base.'.'.$extension
            : $base;

        return $this->uniqueName($candidate, $existingStoredNames);
    }

    public function sanitizeName(string $name): string
    {
        $name = preg_replace('/[\\\\\/\:\*\?\"\<\>\|]+/', ' ', $name) ?? $name;
        $name = preg_replace('/\s+/', ' ', trim($name)) ?? trim($name);

        return $name !== '' ? $name : 'file';
    }

    protected function uniqueName(string $filename, array $existing): string
    {
        $existingLookup = array_map('mb_strtolower', $existing);

        if (! in_array(mb_strtolower($filename), $existingLookup, true)) {
            return $filename;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $counter = 2;

        do {
            $candidate = $extension !== ''
                ? sprintf('%s (%d).%s', $basename, $counter, $extension)
                : sprintf('%s (%d)', $basename, $counter);
            $counter++;
        } while (in_array(mb_strtolower($candidate), $existingLookup, true));

        return $candidate;
    }
}
