<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RightOfReplyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Permohonan Hak Jawab dari publik, masuk antrean Pemred.
 *
 * @property int $id
 * @property int|null $post_id
 * @property string $requester_name
 * @property string $requester_email
 * @property string|null $requester_phone
 * @property string $statement
 * @property RightOfReplyStatus $status
 * @property int|null $handled_by
 * @property string|null $resolution_notes
 * @property Carbon|null $handled_at
 */
class RightOfReplyRequest extends Model
{
    /**
     * Kolom yang boleh diisi massal dari form publik.
     * `ip_hash` dan `handled_by` dikelola internal, jadi tidak ada di sini.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'requester_name',
        'requester_email',
        'requester_phone',
        'statement',
        'status',
        'resolution_notes',
        'handled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RightOfReplyStatus::class,
            'handled_at' => 'datetime',
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
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Scope: permohonan yang belum selesai ditangani.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', [
            RightOfReplyStatus::PENDING->value,
            RightOfReplyStatus::REVIEWED->value,
        ]);
    }

    /**
     * Scope: urutan antrean Pemred.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeQueueOrder(Builder $query): void
    {
        $query->orderByRaw(
            "FIELD(status, 'pending', 'reviewed', 'published', 'rejected')"
        )->orderBy('created_at');
    }
}
