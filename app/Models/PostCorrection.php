<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Ralat resmi untuk artikel PUBLISHED.
 *
 * Koreksi faktual wajib melalui tabel ini dan dirender sebagai kotak ralat
 * di halaman artikel tanpa menghapus jejak histori.
 *
 * @property int $id
 * @property int $post_id
 * @property int|null $actor_id
 * @property string $summary
 * @property Carbon $published_at
 */
class PostCorrection extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'actor_id',
        'summary',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Scope: terbaru lebih dulu.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeNewestFirst(Builder $query): void
    {
        $query->orderByDesc('published_at')->orderByDesc('id');
    }
}
