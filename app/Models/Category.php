<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property string $badge_color
 * @property int $sort_order
 * @property bool $is_active
 * @property int|null $redirect_to_category_id
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * Slug yang dilarang dipakai kategori karena bertabrakan dengan
     * route sistem. Divalidasi di CategoryRequest.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = [
        'cari',
        'indeks',
        'tag',
        'penulis',
        'redaksi',
        'akun',
        'admin',
        'newsroom',
        'api',
        'rss',
        'sitemap',
        'login',
        'register',
        'hak-jawab',
        'up',
        'storage',
        'kebijakan-privasi',
        'syarat-ketentuan',
        'pedoman-media-siber',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'badge_color',
        'description',
        'sort_order',
        'is_active',
        'redirect_to_category_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Kategori tujuan pengalihan bila kategori ini dinonaktifkan.
     *
     * @return BelongsTo<Category, $this>
     */
    public function redirectTarget(): BelongsTo
    {
        return $this->belongsTo(self::class, 'redirect_to_category_id');
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Naskah berstatus PUBLISHED pada kategori ini.
     *
     * @return HasMany<Post, $this>
     */
    public function publishedPosts(): HasMany
    {
        return $this->posts()->where('status', PostStatus::PUBLISHED->value);
    }

    /**
     * Desk-editor yang ditugaskan pada rubrik ini.
     *
     * @return BelongsToMany<User, $this>
     */
    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'category_user')
            ->withPivot('assigned_at');
    }

    /**
     * Scope: hanya kategori aktif, urut sort_order.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope: urutan tampil di navbar.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope: kategori tingkat atas (tanpa induk).
     *
     * @param  Builder<$this>  $query
     */
    public function scopeRoot(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * Warna badge aman-untuk-CSS. Divalidasi dengan regex hex 7 karakter;
     * bila tidak valid, jatuh ke warna netral agar tidak menghasilkan CSS rusak.
     */
    public function safeBadgeColor(): string
    {
        $color = (string) $this->badge_color;

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1
            ? $color
            : '#334155';
    }

    /**
     * Kategori yang dinonaktifkan dan punya tujuan dialihkan dengan 301.
     */
    public function shouldRedirect(): bool
    {
        return ! $this->is_active && $this->redirect_to_category_id !== null;
    }

    /**
     * Apakah slug termasuk yang dilarang.
     */
    public static function isReservedSlug(string $slug): bool
    {
        return in_array($slug, self::RESERVED_SLUGS, true);
    }
}
