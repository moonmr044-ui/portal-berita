<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Epaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'edition_number',
        'edition_date',
        'title',
        'description',
        'file_path',
        'downloads_count',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'edition_date' => 'date',
            'downloads_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasCustomPdf(): bool
    {
        return ! empty($this->file_path) && Storage::disk('public')->exists($this->file_path);
    }

    public static function currentActive(): ?self
    {
        if (! Schema::hasTable('epapers')) {
            return null;
        }

        $epaper = self::query()
            ->where('is_active', true)
            ->orderByDesc('edition_date')
            ->orderByDesc('id')
            ->first();

        if (! $epaper && self::query()->count() === 0) {
            $latestPost = Post::published()->latestPublished()->first();
            $epaper = self::query()->create([
                'edition_number' => 'EDISI #' . now()->format('ymd'),
                'edition_date' => $latestPost?->published_at?->toDateString() ?? now()->toDateString(),
                'title' => $latestPost?->title ?? 'Edisi Cetak Harian Ruang Data',
                'description' => 'Format digital resolusi tinggi dari kompilasi berita utama Ruang Data, gratis untuk seluruh pembaca.',
                'downloads_count' => 0,
                'is_active' => true,
            ]);
        }

        return $epaper;
    }
}
