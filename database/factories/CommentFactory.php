<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory()->reader(),
            'parent_id' => null,
            'body' => fake()->paragraph(3),
            'status' => CommentStatus::PENDING,
        ];
    }

    /**
     * Komentar yang sudah disetujui moderator.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CommentStatus::APPROVED,
        ]);
    }

    /**
     * Komentar yang ditandai spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CommentStatus::SPAM,
        ]);
    }

    /**
     * Komentar yang ditolak moderator.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CommentStatus::REJECTED,
        ]);
    }

    /**
     * Balasan atas komentar induk.
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'post_id' => $parent->post_id,
            'parent_id' => $parent->getKey(),
        ]);
    }

    /**
     * Komentar yang sudah dilaporkan beberapa kali.
     */
    public function reported(int $count = Comment::AUTO_HIDE_REPORT_THRESHOLD): static
    {
        return $this->state(fn (array $attributes): array => [
            'reported_count' => $count,
        ]);
    }
}
