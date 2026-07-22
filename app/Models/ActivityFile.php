<?php

namespace App\Models;

use App\Enums\ActivityFileType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'activity_id',
        'type',
        'original_name',
        'stored_name',
        'drive_file_id',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'type' => ActivityFileType::class,
            'size' => 'integer',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
