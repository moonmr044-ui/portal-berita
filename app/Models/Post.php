<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $category_id
 * @property int $author_id
 * @property int|null $editor_id
 * @property int|null $claimed_by
 * @property Carbon|null $claimed_at
 * @property Carbon|null $claim_expires_at
 * @property int|null $featured_media_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $body
 * @property PostStatus $status
 * @property Carbon|null $published_at
 * @property string|null $editorial_notes
 * @property string|null $takedown_reason
 * @property int $views_count
 * @property int $lock_version
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $source_url
 * @property string|null $source_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read User|null $author
 * @property-read User|null $editor
 * @property-read Media|null $featuredMedia
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Durasi klaim editor sebelum otomatis dilepas oleh job terjadwal.
     */
    public const CLAIM_DURATION_HOURS = 4;

    /**
     * Jumlah maksimal revisi yang disimpan per artikel.
     */
    public const MAX_REVISIONS = 50;

    /**
     * Minimal jumlah paragraf `<p>` untuk boleh diajukan review.
     */
    public const MIN_PARAGRAPHS = 4;

    /**
     * Kolom yang boleh diisi massal.
     *
     * Catatan: Laravel mengabaikan `$guarded` bila `$fillable` terisi. Karena
     * itu model ini memakai `$guarded` sebagai SATU-SATUNYA sumber kebenaran
     * (daftar di bawah), sehingga views_count, lock_version, dan data klaim
     * tidak pernah dapat diisi dari request pengguna.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'views_count',
        'lock_version',
        'claimed_by',
        'claimed_at',
        'claim_expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'claimed_at' => 'datetime',
            'claim_expires_at' => 'datetime',
            'views_count' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    /**
     * Editor yang sedang memegang klaim naskah ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    /**
     * @return HasMany<PostStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(PostStatusHistory::class)->latest('created_at');
    }

    /**
     * @return HasMany<PostRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class)->latest('created_at');
    }

    /**
     * @return HasMany<PostCorrection, $this>
     */
    public function corrections(): HasMany
    {
        return $this->hasMany(PostCorrection::class)->latest('published_at');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag')->withPivot('created_at');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Reaction, $this>
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    /**
     * Hanya artikel PUBLISHED dan sudah melewati waktu terbit.
     *
     * @param  Builder<$this>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::PUBLISHED->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Artikel yang tampil di ruang publik (sama dengan published untuk saat ini).
     *
     * @param  Builder<$this>  $query
     */
    public function scopePubliclyVisible(Builder $query): void
    {
        $query->published();
    }

    /**
     * Artikel berstatus tertentu.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeStatus(Builder $query, PostStatus $status): void
    {
        $query->where('status', $status->value);
    }

    /**
     * Naskah pada kategori tertentu.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeInCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }

    /**
     * Naskah milik penulis tertentu.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeAuthoredBy(Builder $query, int $userId): void
    {
        $query->where('author_id', $userId);
    }

    /**
     * Naskah yang klaimnya belum kedaluwarsa.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeClaimActive(Builder $query): void
    {
        $query->whereNotNull('claimed_by')
            ->where('claim_expires_at', '>', now());
    }

    /**
     * Naskah yang klaimnya sudah kedaluwarsa, siap dilepas job terjadwal.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeClaimExpired(Builder $query): void
    {
        $query->whereNotNull('claimed_by')
            ->whereNotNull('claim_expires_at')
            ->where('claim_expires_at', '<', now());
    }

    /**
     * Urutan terbaru berdasarkan waktu terbit.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeLatestPublished(Builder $query): void
    {
        $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * Eager loading minimum untuk daftar berita (Zero N+1).
     *
     * @param  Builder<$this>  $query
     */
    public function scopeWithListRelations(Builder $query): void
    {
        $query->with([
            'category:id,name,slug,badge_color',
            'author:id,name',
            'editor:id,name',
            'featuredMedia',
        ])->withCount([
            'reactions',
            'comments' => fn ($q) => $q->approved(),
        ]);
    }

    /**
     * Eager loading lengkap untuk halaman baca artikel.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeWithDetailRelations(Builder $query): void
    {
        $query->with([
            'category:id,name,slug,badge_color,parent_id',
            'author:id,name',
            'editor:id,name',
            'featuredMedia',
            'tags:id,name,slug',
            'corrections.actor:id,name',
        ]);
    }

    /**
     * Pencarian FULLTEXT pada judul, ringkasan, dan isi.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeFulltextSearch(Builder $query, string $term): void
    {
        $sanitized = preg_replace('/[+\-><()~*"@]+/', ' ', $term) ?? '';
        $sanitized = trim(preg_replace('/\s+/', ' ', $sanitized) ?? '');

        if ($sanitized === '') {
            return;
        }

        $query->whereFullText(['title', 'excerpt', 'body'], $sanitized);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper FSM & klaim
    |--------------------------------------------------------------------------
    */

    /**
     * Transisi status diizinkan menurut matriks PostStatus.
     */
    public function canTransitionTo(PostStatus $to): bool
    {
        return $this->status->canTransitionTo($to);
    }

    /**
     * Apakah artikel sudah terbit dan terlihat publik.
     */
    public function isPublished(): bool
    {
        return $this->status === PostStatus::PUBLISHED
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /**
     * Apakah artikel sedang dipegang editor dengan klaim masih aktif.
     */
    public function hasActiveClaim(): bool
    {
        return $this->claimed_by !== null
            && $this->claim_expires_at !== null
            && $this->claim_expires_at->isFuture();
    }

    /**
     * Apakah klaim sudah kedaluwarsa dan boleh diambil editor lain.
     */
    public function hasExpiredClaim(): bool
    {
        return $this->claimed_by !== null
            && $this->claim_expires_at !== null
            && $this->claim_expires_at->isPast();
    }

    /**
     * URL kanonik artikel.
     */
    public function canonicalPath(): string
    {
        return sprintf('/%s/%s', $this->category?->slug, $this->slug);
    }

    /**
     * Jumlah paragraf bermakna pada body (dipakai syarat pengajuan review
     * dan penyisipan "Baca Juga").
     */
    public function paragraphCount(): int
    {
        $body = (string) $this->body;

        if ($body === '') {
            return 0;
        }

        preg_match_all('#<p\b[^>]*>(.*?)</p>#is', $body, $matches);

        $count = 0;

        foreach ($matches[1] as $inner) {
            if (trim(strip_tags($inner)) !== '') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Judul untuk SEO: memakai seo_title bila diisi.
     */
    public function metaTitle(): string
    {
        $seo = trim((string) $this->seo_title);

        return $seo !== '' ? $seo : $this->title;
    }

    /**
     * Deskripsi meta: memakai seo_description, lalu excerpt, lalu potongan body.
     */
    public function metaDescription(): string
    {
        $seo = trim((string) $this->seo_description);

        if ($seo !== '') {
            return $seo;
        }

        $excerpt = trim(strip_tags((string) $this->excerpt));

        if ($excerpt !== '') {
            return mb_strimwidth($excerpt, 0, 300, '…');
        }

        return mb_strimwidth(trim(strip_tags((string) $this->body)), 0, 300, '…');
    }

    /**
     * Nama tampilan penulis / reporter artikel.
     * Menggunakan author_name custom bila ada, atau nama akun pembuat.
     */
    public function authorDisplayName(): string
    {
        if (! empty($this->author_name)) {
            return $this->author_name;
        }

        return $this->author?->displayName() ?? 'Tim Redaksi';
    }

    /**
     * Kredit redaksi: "Reporter: X | Editor: Y".
     */
    public function byline(): string
    {
        $reporter = $this->authorDisplayName();
        $editor = $this->editor?->displayName();

        return $editor !== null && $editor !== $reporter
            ? sprintf('Reporter: %s | Editor: %s', $reporter, $editor)
            : sprintf('Reporter: %s', $reporter);
    }

    /**
     * Apakah artikel memiliki Ralat resmi.
     */
    public function hasCorrections(): bool
    {
        return $this->corrections()->exists();
    }

    /**
     * Nama sumber atribusi untuk artikel hasil ingest RSS.
     */
    public function attribution(): ?string
    {
        if ($this->source_name === null && $this->source_url === null) {
            return null;
        }

        return $this->source_name ?? $this->source_url;
    }
}
