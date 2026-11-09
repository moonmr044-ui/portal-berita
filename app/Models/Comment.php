<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommentStatus;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $body
 * @property CommentStatus $status
 * @property int $reported_count
 * @property Carbon|null $created_at
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Ambang jumlah laporan yang otomatis menyembunyikan komentar
     * sampai moderator meninjau.
     */
    public const AUTO_HIDE_REPORT_THRESHOLD = 5;

    /**
     * Kolom yang boleh diisi massal dari form komentar.
     * `ip_hash` dan `reported_count` dikelola internal, jadi tidak ada di sini.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'body',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'reported_count' => 'integer',
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
     * Komentar induk (untuk balasan bersarang).
     *
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    /**
     * Scope: hanya komentar yang disetujui (tampil publik).
     *
     * @param  Builder<$this>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', CommentStatus::APPROVED->value);
    }

    /**
     * Scope: menunggu moderasi.
     *
     * @param  Builder<$this>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', CommentStatus::PENDING->value);
    }

    /**
     * Scope: komentar tingkat atas saja (untuk paginasi diskusi).
     *
     * @param  Builder<$this>  $query
     */
    public function scopeRoot(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * Scope: komentar pada artikel tertentu.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeForPost(Builder $query, int $postId): void
    {
        $query->where('post_id', $postId);
    }

    /**
     * Komentar yang perlu ditinjau moderator.
     */
    public function needsModeration(): bool
    {
        return $this->status === CommentStatus::PENDING
            || $this->reported_count >= self::AUTO_HIDE_REPORT_THRESHOLD;
    }

    /**
     * Apakah komentar ini masih tampil di halaman publik.
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status->isPubliclyVisible()
            && $this->reported_count < self::AUTO_HIDE_REPORT_THRESHOLD;
    }

    /**
     * Inisial pembaca, dipakai saat komentar dianonimkan setelah
     * penghapusan akun tanpa merusak struktur diskusi.
     */
    public function authorLabel(): string
    {
        return $this->user?->displayName() ?? 'Pembaca';
    }
}
