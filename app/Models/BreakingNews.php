<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Item ticker Breaking News di Top Utility Bar.
 *
 * Hanya item aktif dalam rentang starts_at..ends_at yang dirender.
 *
 * @property int $id
 * @property string $title
 * @property string|null $url
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property bool $is_active
 * @property int|null $created_by
 */
class BreakingNews extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'url',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: item yang sedang tayang pada rentang waktunya.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeCurrentlyActive(Builder $query): void
    {
        $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(static function (Builder $q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderByDesc('starts_at');
    }

    /**
     * Apakah item sedang dalam rentang tayang.
     */
    public function isLive(): bool
    {
        if (! $this->is_active || $this->starts_at->isFuture()) {
            return false;
        }

        return $this->ends_at === null || $this->ends_at->isFuture();
    }
}
