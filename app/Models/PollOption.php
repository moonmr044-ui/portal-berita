<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'label',
        'votes_count',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'votes_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Poll, $this>
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * @return HasMany<PollVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function percentage(int $totalVotes): int
    {
        if ($totalVotes <= 0) {
            return 0;
        }

        return (int) round(($this->votes_count / $totalVotes) * 100);
    }
}
