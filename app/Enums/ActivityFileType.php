<?php

namespace App\Enums;

enum ActivityFileType: string
{
    case Basic = 'dasar';
    case Evidence = 'bukti';

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Berkas Dasar',
            self::Evidence => 'Bukti Kegiatan',
        };
    }
}
