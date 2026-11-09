<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(8);

        return [
            'category_id' => Category::factory(),
            'author_id' => User::factory(),
            'editor_id' => null,
            'featured_media_id' => null,
            'title' => Str::title(rtrim($title, '.')),
            'slug' => $this->buildSlug($title),
            'excerpt' => fake()->paragraph(2),
            'body' => $this->buildBody(),
            'status' => PostStatus::DRAFT,
            'editorial_notes' => null,
            'takedown_reason' => null,
            'published_at' => null,
            'seo_title' => null,
            'seo_description' => null,
            'source_url' => null,
            'source_name' => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | State FSM
    |--------------------------------------------------------------------------
    */

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::DRAFT,
            'published_at' => null,
            'editor_id' => null,
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::UNDER_REVIEW,
            'published_at' => null,
        ]);
    }

    public function rejected(string $notes = 'Naskah belum memenuhi kaidah 5W1H dan perlu konfirmasi narasumber tambahan.'): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::REJECTED,
            'editorial_notes' => $notes,
            'published_at' => null,
        ]);
    }

    public function scheduled(?DateTimeInterface $at = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::SCHEDULED,
            'published_at' => $at ?? now()->addHours(3),
            'editor_id' => User::factory(),
        ]);
    }

    public function published(?DateTimeInterface $at = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::PUBLISHED,
            'published_at' => $at ?? now()->subHour(),
            'editor_id' => User::factory(),
        ]);
    }

    public function takedown(string $reason = 'Permintaan klarifikasi hukum dari pihak terkait sedang diproses.'): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::TAKEDOWN,
            'takedown_reason' => $reason,
            'published_at' => now()->subDays(2),
            'editor_id' => User::factory(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | State tambahan
    |--------------------------------------------------------------------------
    */

    public function withFeaturedMedia(): static
    {
        return $this->state(fn (array $attributes): array => [
            'featured_media_id' => Media::factory(),
        ]);
    }

    /**
     * Naskah yang sedang dipegang editor dengan klaim aktif.
     */
    public function claimedBy(User $editor, int $hours = Post::CLAIM_DURATION_HOURS): static
    {
        return $this->state(fn (array $attributes): array => [
            'claimed_by' => $editor->getKey(),
            'claimed_at' => now(),
            'claim_expires_at' => now()->addHours($hours),
        ]);
    }

    /**
     * Naskah dengan klaim yang sudah kedaluwarsa.
     */
    public function withExpiredClaim(User $editor): static
    {
        return $this->state(fn (array $attributes): array => [
            'claimed_by' => $editor->getKey(),
            'claimed_at' => now()->subHours(6),
            'claim_expires_at' => now()->subHours(2),
        ]);
    }

    /**
     * Slug yang dibangun konsisten dengan aturan produksi:
     * Str::slug(Str::limit($title, 80, '')) . '-' . strtolower(Str::random(6)).
     */
    private function buildSlug(string $title): string
    {
        return Str::slug(Str::limit($title, 80, '')).'-'.strtolower(Str::random(6));
    }

    /**
     * Body empat paragraf agar memenuhi syarat minimum pengajuan review.
     */
    private function buildBody(int $paragraphs = 4): string
    {
        $result = '';

        for ($i = 0; $i < $paragraphs; $i++) {
            $result .= sprintf('<p>%s</p>', fake()->paragraph(5));
        }

        return $result;
    }
}
