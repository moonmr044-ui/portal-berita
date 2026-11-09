<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 */
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag')->withPivot('created_at');
    }

    /**
     * Hanya artikel PUBLISHED pada tag ini.
     *
     * @return BelongsToMany<Post, $this>
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->posts()->where('status', PostStatus::PUBLISHED->value);
    }

    /**
     * Scope: urut abjad.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('name');
    }

    /**
     * Scope: tag dengan frekuensi artikel PUBLISHED tertinggi dalam N hari.
     *
     * @param  Builder<$this>  $query
     */
    public function scopePopular(Builder $query, int $days = 7): void
    {
        $query->select('tags.*')
            ->join('post_tag', 'post_tag.tag_id', '=', 'tags.id')
            ->join('posts', 'posts.id', '=', 'post_tag.post_id')
            ->where('posts.status', PostStatus::PUBLISHED->value)
            ->where('posts.published_at', '>=', now()->subDays($days))
            ->groupBy('tags.id', 'tags.name', 'tags.slug')
            ->orderByRaw('COUNT(posts.id) DESC')
            ->orderBy('tags.name');
    }
}
