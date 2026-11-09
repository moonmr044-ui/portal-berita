<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Memetakan slug lama artikel ke artikel terkini (redirect 301).
 *
 * @property int $id
 * @property string $old_slug
 * @property int $post_id
 * @property Carbon $created_at
 */
class SlugRedirect extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'old_slug',
        'post_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
     * Scope: cari berdasarkan slug lama.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeForSlug(Builder $query, string $slug): void
    {
        $query->where('old_slug', $slug);
    }
}
