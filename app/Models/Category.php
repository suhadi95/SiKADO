<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function badgeBackgroundColor(): string
    {
        $color = $this->color ?: '#64748B';

        if (! preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
            return '#64748B';
        }

        if (strlen($color) === 4) {
            $color = sprintf('#%s%s%s%s%s%s', $color[1], $color[1], $color[2], $color[2], $color[3], $color[3]);
        }

        return strtoupper($color);
    }

    /**
     * Hitam atau putih berdasarkan luminance latar badge.
     */
    public function badgeTextColor(): string
    {
        $hex = ltrim($this->badgeBackgroundColor(), '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Relative luminance (sederhana, cukup untuk kontras teks).
        $luminance = ((0.299 * $r) + (0.587 * $g) + (0.114 * $b)) / 255;

        return $luminance > 0.6 ? '#111827' : '#FFFFFF';
    }
}
