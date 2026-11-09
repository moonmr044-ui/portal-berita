<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

/**
 * @property int $id
 * @property int $uploader_id
 * @property string $disk
 * @property string $path
 * @property int|null $width
 * @property int|null $height
 * @property string $mime_type
 * @property int $size_bytes
 * @property string|null $alt_text
 * @property string|null $caption
 * @property string|null $credit
 * @property array<string, string>|null $variants
 */
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * Tabel tidak memakai kolom `updated_at` semantik bisnis, tetapi tetap
     * dipertahankan untuk konsistensi Eloquent.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uploader_id',
        'disk',
        'path',
        'width',
        'height',
        'mime_type',
        'size_bytes',
        'alt_text',
        'caption',
        'credit',
        'variants',
        'checksum',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'size_bytes' => 'integer',
            'variants' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * Artikel yang memakai media ini sebagai gambar utama.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Post, $this>
     */
    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class, 'featured_media_id');
    }

    /**
     * URL publik untuk varian tertentu (default: berkas utama).
     */
    public function url(?string $variant = null): string
    {
        $path = $variant !== null
            ? ($this->variants[$variant] ?? $this->path)
            : $this->path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $url = Storage::disk($this->disk)->url($path);
        $parsed = parse_url($url);

        return ($parsed['path'] ?? $url).(isset($parsed['query']) ? '?'.$parsed['query'] : '');
    }

    /**
     * Peta srcset untuk atribut `srcset` (`400w`, `800w`, `1200w`).
     *
     * @return array<string, string>
     */
    public function srcset(): array
    {
        $srcset = [];

        foreach ($this->variants ?? [] as $width => $path) {
            $srcset[$width] = Storage::disk($this->disk)->url($path);
        }

        return $srcset;
    }

    /**
     * Atribut `srcset` siap pakai, contoh: "url 400w, url 800w".
     */
    public function srcsetAttribute(): string
    {
        $parts = [];

        foreach ($this->srcset() as $width => $url) {
            $parts[] = sprintf('%s %sw', $url, $width);
        }

        return implode(', ', $parts);
    }

    /**
     * Rasio aspek untuk reservasi ruang (mencegah layout shift).
     */
    public function aspectRatio(): ?float
    {
        if ($this->width === null || $this->height === null || $this->height === 0) {
            return null;
        }

        return round($this->width / $this->height, 4);
    }

    /**
     * Ukuran berkas dalam format manusiawi.
     */
    public function humanSize(): string
    {
        return Number::fileSize($this->size_bytes, precision: 1);
    }

    /**
     * Teks alternatif wajib untuk aksesibilitas.
     */
    public function altOrFallback(string $fallback = ''): string
    {
        $alt = trim((string) $this->alt_text);

        return $alt !== '' ? $alt : $fallback;
    }
}
