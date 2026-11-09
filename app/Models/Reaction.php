<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReactionType;
use Database\Factories\ReactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property ReactionType $type
 */
class Reaction extends Model
{
    /** @use HasFactory<ReactionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ReactionType::class,
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: reaksi pada artikel tertentu.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeForPost(Builder $query, int $postId): void
    {
        $query->where('post_id', $postId);
    }

    /**
     * Rekapitulasi jumlah per jenis reaksi untuk satu artikel.
     *
     * @return array<string, int>
     */
    public static function tallyForPost(int $postId): array
    {
        $counts = self::query()
            ->forPost($postId)
            ->selectRaw('type, COUNT(*) AS total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        $tally = [];

        foreach (ReactionType::cases() as $type) {
            $tally[$type->value] = (int) ($counts[$type->value] ?? 0);
        }

        return $tally;
    }
}
