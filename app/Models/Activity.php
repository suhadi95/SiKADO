<?php

namespace App\Models;

use App\Enums\ActivityFileType;
use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Activity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'activity_date',
        'name',
        'requires_evidence',
        'notes',
        'drive_folder_id',
        'drive_folder_url',
        'drive_sync_failed',
    ];

    protected $appends = [
        'status',
        'status_label',
        'evidence_count',
        'basic_count',
        'files_count',
        'drive_folder_name',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'requires_evidence' => 'boolean',
            'drive_sync_failed' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ActivityFile::class);
    }

    public function evidenceFiles(): HasMany
    {
        return $this->files()->where('type', ActivityFileType::Evidence->value);
    }

    public function basicFiles(): HasMany
    {
        return $this->files()->where('type', ActivityFileType::Basic->value);
    }

    public function getEvidenceCountAttribute(): int
    {
        if (array_key_exists('evidence_files_count', $this->attributes)) {
            return (int) $this->attributes['evidence_files_count'];
        }

        if ($this->relationLoaded('files')) {
            return $this->files
                ->where('type', ActivityFileType::Evidence)
                ->count();
        }

        return $this->evidenceFiles()->count();
    }

    public function getBasicCountAttribute(): int
    {
        if (array_key_exists('basic_files_count', $this->attributes)) {
            return (int) $this->attributes['basic_files_count'];
        }

        if ($this->relationLoaded('files')) {
            return $this->files
                ->where('type', ActivityFileType::Basic)
                ->count();
        }

        return $this->basicFiles()->count();
    }

    public function getFilesCountAttribute(): int
    {
        if (array_key_exists('files_count', $this->attributes)) {
            return (int) $this->attributes['files_count'];
        }

        if ($this->relationLoaded('files')) {
            return $this->files->count();
        }

        return $this->files()->count();
    }

    public function getStatusAttribute(): ActivityStatus
    {
        if ($this->drive_sync_failed || blank($this->drive_folder_id)) {
            return ActivityStatus::DriveFolderIssue;
        }

        if (! $this->requires_evidence) {
            return ActivityStatus::NoEvidenceRequired;
        }

        if ($this->evidence_count >= 1) {
            return ActivityStatus::Complete;
        }

        if ($this->basic_count === 0) {
            return ActivityStatus::MissingBasic;
        }

        return ActivityStatus::NeedsEvidence;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getDriveFolderNameAttribute(): string
    {
        $date = $this->activity_date instanceof Carbon
            ? $this->activity_date
            : Carbon::parse($this->activity_date);

        return $date->format('y-m-d').' '.$this->name;
    }

    public function isPendingEvidence(): bool
    {
        return $this->requires_evidence && $this->evidence_count === 0;
    }

    public function scopeNeedsEvidence(Builder $query): Builder
    {
        return $query
            ->where('requires_evidence', true)
            ->whereDoesntHave('files', function (Builder $q) {
                $q->where('type', ActivityFileType::Evidence->value);
            });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['name'] ?? null, function (Builder $q, string $name) {
                $q->where('name', 'like', '%'.$name.'%');
            })
            ->when($filters['category_id'] ?? null, function (Builder $q, $categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($filters['month'] ?? null, function (Builder $q, $month) {
                $q->whereMonth('activity_date', (int) $month);
            })
            ->when($filters['year'] ?? null, function (Builder $q, $year) {
                $q->whereYear('activity_date', (int) $year);
            })
            ->when($filters['status'] ?? null, function (Builder $q, string $status) {
                $this->applyStatusFilter($q, $status);
            });
    }

    public function scopeSortedByDate(Builder $query, ?string $sort = 'date_desc'): Builder
    {
        return match ($sort) {
            'date_asc' => $query->orderBy('activity_date')->orderBy('id'),
            default => $query->orderByDesc('activity_date')->orderByDesc('id'),
        };
    }

    protected function applyStatusFilter(Builder $query, string $status): void
    {
        match ($status) {
            ActivityStatus::DriveFolderIssue->value => $query->where(function (Builder $q) {
                $q->where('drive_sync_failed', true)->orWhereNull('drive_folder_id');
            }),
            ActivityStatus::NoEvidenceRequired->value => $query
                ->where('requires_evidence', false)
                ->where('drive_sync_failed', false)
                ->whereNotNull('drive_folder_id'),
            ActivityStatus::Complete->value => $query
                ->where('requires_evidence', true)
                ->where('drive_sync_failed', false)
                ->whereNotNull('drive_folder_id')
                ->whereHas('files', fn (Builder $q) => $q->where('type', ActivityFileType::Evidence->value)),
            ActivityStatus::MissingBasic->value => $query
                ->where('requires_evidence', true)
                ->where('drive_sync_failed', false)
                ->whereNotNull('drive_folder_id')
                ->whereDoesntHave('files', fn (Builder $q) => $q->where('type', ActivityFileType::Evidence->value))
                ->whereDoesntHave('files', fn (Builder $q) => $q->where('type', ActivityFileType::Basic->value)),
            ActivityStatus::NeedsEvidence->value => $query
                ->where('requires_evidence', true)
                ->where('drive_sync_failed', false)
                ->whereNotNull('drive_folder_id')
                ->whereDoesntHave('files', fn (Builder $q) => $q->where('type', ActivityFileType::Evidence->value))
                ->whereHas('files', fn (Builder $q) => $q->where('type', ActivityFileType::Basic->value)),
            default => null,
        };
    }
}
