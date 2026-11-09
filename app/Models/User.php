<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property bool $is_crew
 * @property bool $is_active
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $two_factor_confirmed_at
 * @property Carbon|null $last_login_at
 * @property Carbon|null $created_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Atribut yang boleh diisi massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_path',
        'password',
        'is_crew',
        'is_active',
        'email_verified_at',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_crew' => 'boolean',
            'is_active' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Kategori (rubrik) yang ditugaskan kepada desk-editor ini.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function assignedCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_user')
            ->withPivot('assigned_at');
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Naskah yang pernah disunting pengguna ini.
     *
     * @return HasMany<Post, $this>
     */
    public function editedPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'editor_id');
    }

    /**
     * Naskah yang sedang dipegang (diklaim) pengguna ini.
     *
     * @return HasMany<Post, $this>
     */
    public function claimedPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'claimed_by');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Bookmark, $this>
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * @return HasMany<Reaction, $this>
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function uploadedMedia(): HasMany
    {
        return $this->hasMany(Media::class, 'uploader_id');
    }

    /**
     * Apakah 2FA TOTP sudah aktif dan terkonfirmasi.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null
            && $this->two_factor_secret !== null;
    }

    /**
     * Akun kru wajib mengaktifkan 2FA sebelum boleh masuk newsroom.
     */
    public function requiresTwoFactor(): bool
    {
        return $this->is_crew;
    }

    /**
     * Reader wajib memverifikasi email sebelum berkomentar, bereaksi,
     * atau menyimpan bookmark.
     */
    public function hasVerifiedEmailOrFail(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Scope: hanya akun yang aktif.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope: hanya akun kru.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeCrew(Builder $query): void
    {
        $query->where('is_crew', true);
    }

    /**
     * Scope: hanya akun pembaca (bukan kru).
     *
     * @param  Builder<$this>  $query
     */
    public function scopeReaders(Builder $query): void
    {
        $query->where('is_crew', false);
    }

    /**
     * Nama tampilan penulis untuk kredit artikel.
     */
    public function displayName(): string
    {
        return $this->name;
    }

    /**
     * URL foto profil pengguna jika tersedia di storage publik.
     */
    public function avatarUrl(): ?string
    {
        if (empty($this->avatar_path)) {
            return null;
        }

        if (str_starts_with($this->avatar_path, 'http://') || str_starts_with($this->avatar_path, 'https://')) {
            return $this->avatar_path;
        }

        return asset('storage/'.ltrim($this->avatar_path, '/'));
    }

    /**
     * Inisial huruf depan nama pengguna untuk avatar fallback.
     */
    public function initials(): string
    {
        $trimmed = trim($this->name);
        if ($trimmed === '') {
            return 'P';
        }

        return mb_strtoupper(mb_substr($trimmed, 0, 1));
    }

    /**
     * ID kategori yang ditugaskan, untuk pembatasan Policy desk-editor.
     *
     * @return list<int>
     */
    public function assignedCategoryIds(): array
    {
        return $this->assignedCategories()->pluck('categories.id')->all();
    }
}
