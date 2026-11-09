<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EditorialPickSlot;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Slot kurasi hero homepage yang diisi Pemred.
 *
 * Bila post_id NULL atau artikelnya tidak PUBLISHED, homepage otomatis
 * fallback ke artikel PUBLISHED terbaru tanpa duplikasi.
 *
 * @property int $id
 * @property EditorialPickSlot $slot
 * @property int|null $post_id
 * @property int|null $updated_by
 */
class EditorialPick extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'slot',
        'post_id',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot' => EditorialPickSlot::class,
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
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Apakah slot ini berisi artikel yang benar-benar tampil publik.
     */
    public function isFilled(): bool
    {
        return $this->post_id !== null
            && $this->post !== null
            && $this->post->status === PostStatus::PUBLISHED;
    }

    /**
     * Baris slot lengkap (headline + pick_1..pick_4) dengan relasi artikel.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function slotsWithPosts(): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()
            ->with(['post' => static function (BelongsTo $relation): void {
                $relation->with([
                    'category:id,name,slug,badge_color',
                    'author:id,name',
                    'featuredMedia',
                ]);
            }])
            ->get()
            ->sortBy(static fn (self $pick): int => $pick->slot->sortOrder())
            ->values();
    }
}
