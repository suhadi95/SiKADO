<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case NeedsEvidence = 'butuh_bukti';
    case Complete = 'lengkap';
    case NoEvidenceRequired = 'tidak_memerlukan_bukti';
    case MissingBasic = 'belum_ada_dasar';
    case DriveFolderIssue = 'folder_drive_bermasalah';

    public function label(): string
    {
        return match ($this) {
            self::NeedsEvidence => 'Butuh Bukti',
            self::Complete => 'Lengkap',
            self::NoEvidenceRequired => 'Tidak Memerlukan Bukti',
            self::MissingBasic => 'Belum Ada Berkas Dasar',
            self::DriveFolderIssue => 'Folder Drive Bermasalah',
        };
    }

    public function colorClasses(): string
    {
        return match ($this) {
            self::NeedsEvidence => 'bg-amber-100 text-amber-800',
            self::Complete => 'bg-emerald-100 text-emerald-800',
            self::NoEvidenceRequired => 'bg-sky-100 text-sky-800',
            self::MissingBasic => 'bg-orange-100 text-orange-800',
            self::DriveFolderIssue => 'bg-rose-100 text-rose-800',
        };
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::Complete, self::NoEvidenceRequired], true);
    }
}
