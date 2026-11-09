<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Catatan audit setiap transisi FSM artikel.
 *
 * @property int $id
 * @property int $post_id
 * @property string|null $from_status
 * @property string $to_status
 * @property int|null $actor_id
 * @property string|null $reason
 * @property string|null $ip_address
 * @property Carbon $created_at
 */
class PostStatusHistory extends Model
{
    /**
     * Hanya memakai kolom created_at.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'from_status',
        'to_status',
        'actor_id',
        'reason',
        'ip_address',
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
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Keterangan perubahan untuk tampilan histori di panel kru.
     */
    public function description(): string
    {
        $from = $this->from_status ?? 'baru';

        return sprintf('%s -> %s', $from, $this->to_status);
    }
}
