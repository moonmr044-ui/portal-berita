<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'description',
        'edition_label',
        'deadline_label',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PollOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<PollVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalVotes(): int
    {
        return (int) $this->options->sum('votes_count');
    }

    /**
     * Pastikan selalu ada minimal 1 survei aktif di database tanpa perlu seed/tinker manual.
     */
    public static function currentActive(): ?self
    {
        if (! Schema::hasTable('polls')) {
            return null;
        }

        $poll = self::query()
            ->with('options')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $poll && self::query()->count() === 0) {
            $poll = self::query()->create([
                'question' => 'Apakah Anda setuju dengan pembatasan kendaraan pribadi berbasis tarif emisi di pusat perkotaan?',
                'description' => 'Pemerintah daerah mengkaji penerapan zona emisi rendah (LEZ) dengan sanksi retribusi bagi kendaraan di atas 10 tahun.',
                'edition_label' => 'Edisi Pekan Ini',
                'deadline_label' => 'Akhir pekan',
                'is_active' => true,
            ]);

            $poll->options()->createMany([
                [
                    'label' => 'Sangat Setuju (Demi percepatan udara bersih)',
                    'votes_count' => 0,
                    'sort_order' => 1,
                ],
                [
                    'label' => 'Setuju Bersyarat (Jika transportasi publik memadai)',
                    'votes_count' => 0,
                    'sort_order' => 2,
                ],
                [
                    'label' => 'Tidak Setuju (Memberatkan mobilitas masyarakat)',
                    'votes_count' => 0,
                    'sort_order' => 3,
                ],
            ]);

            $poll->load('options');
        }

        return $poll;
    }
}
