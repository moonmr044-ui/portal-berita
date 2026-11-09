<?php

declare(strict_types=1);

namespace Tests\Feature\Newsroom;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostAuthorNameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_publish_post_with_custom_author_name(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Ahmad Arifaie',
            'is_crew' => true,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super-admin');

        $session = [
            'crew_last_activity' => now()->timestamp,
            'email_otp_verified' => true,
        ];

        $category = Category::factory()->create(['name' => 'Politik', 'slug' => 'politik']);

        $response = $this->actingAs($superAdmin)->withSession($session)->post(route('newsroom.posts.store'), [
            'title' => 'Berita Liputan Teman Saya',
            'author_name' => 'Budi Pratama (Wartawan Tamu)',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan berita dari teman.',
            'body' => '<p>Paragraf satu.</p><p>Paragraf dua.</p><p>Paragraf tiga.</p><p>Paragraf empat.</p>',
            'action' => 'publish',
        ]);

        $response->assertRedirect(route('newsroom.posts.index'));

        $post = Post::where('title', 'Berita Liputan Teman Saya')->first();
        $this->assertNotNull($post);
        $this->assertEquals('Budi Pratama (Wartawan Tamu)', $post->authorDisplayName());

        // Cek halaman publik
        $publicResponse = $this->get(route('home'));
        $publicResponse->assertOk();
        $publicResponse->assertSee('Budi Pratama (Wartawan Tamu)');
    }
}
