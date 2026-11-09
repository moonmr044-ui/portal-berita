<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Snapshot naskah setiap kali disimpan (maks 50 revisi terbaru per artikel).
 *
 * @property int $id
 * @property int $post_id
 * @property string|null $title
 * @property string|null $excerpt
 * @property string|null $body
 * @property int|null $actor_id
 * @property int $lock_version
 * @property string|null $note
 * @property Carbon $created_at
 */
class PostRevision extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'title',
        'excerpt',
        'body',
        'actor_id',
        'lock_version',
        'note',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lock_version' => 'integer',
            'created_at' => 'datetime',
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
     * Scope: revisi terbaru lebih dulu.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeNewestFirst(Builder $query): void
    {
        $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Buang revisi lama sehingga hanya MAX_REVISIONS terbaru tersisa.
     */
    public static function pruneForPost(int $postId, int $keep = Post::MAX_REVISIONS): int
    {
        $idsToKeep = self::query()
            ->where('post_id', $postId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($keep)
            ->pluck('id');

        if ($idsToKeep->isEmpty()) {
            return 0;
        }

        return self::query()
            ->where('post_id', $postId)
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }
}
