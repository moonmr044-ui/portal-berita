<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\RoleName;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\Epaper;
use App\Models\Poll;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\NewsPortalDemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class FullPortalSystemAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(NewsPortalDemoSeeder::class);
    }

    private function authSuperAdmin(): User
    {
        $superAdmin = User::where('email', 'ahmadarifaie64@gmail.com')->first();
        if (! $superAdmin) {
            $superAdmin = User::factory()->create([
                'email' => 'ahmadarifaie64@gmail.com',
                'is_crew' => true,
                'is_active' => true,
            ]);
            $superAdmin->assignRole(RoleName::SUPER_ADMIN->value);
        }

        return $superAdmin;
    }

    public function test_01_public_pages_render_successfully(): void
    {
        // 1. Homepage
        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('RUANG');
        $res->assertSee('DATA');

        // 2. Search
        $res = $this->get('/cari?q=Indonesia');
        $res->assertOk();

        // 3. Category show
        $category = Category::where('is_active', true)->first();
        $res = $this->get('/kategori/'.$category->slug);
        $res->assertOk();
        $res->assertSee($category->name);

        // 4. Single article reading
        $post = Post::published()->first();
        $res = $this->get('/'.$post->category->slug.'/'.$post->slug);
        $res->assertOk();
        $res->assertSee($post->title);
    }

    public function test_02_auth_pages_render_successfully(): void
    {
        $res = $this->get('/login');
        $res->assertOk();
        $res->assertSee('Masuk');

        $res = $this->get('/register');
        $res->assertOk();
        $res->assertSee('Daftar Akun Pembaca');
    }

    public function test_03_newsroom_dashboard_and_curation(): void
    {
        $superAdmin = $this->authSuperAdmin();

        $session = [
            'crew_last_activity' => now()->timestamp,
            'email_otp_verified' => true,
        ];

        // Dashboard
        $res = $this->actingAs($superAdmin)->withSession($session)->get('/newsroom');
        $res->assertOk();
        $res->assertSee('Dasbor Redaksi');

        // Kurasi Homepage
        $res = $this->actingAs($superAdmin)->withSession($session)->get('/newsroom/kurasi-homepage');
        $res->assertOk();
        $res->assertSee('Kurasi Hero Homepage');

        // Update kurasi
        $posts = Post::published()->limit(5)->get();
        if ($posts->count() >= 5) {
            $res = $this->actingAs($superAdmin)->withSession($session)->post('/newsroom/kurasi-homepage', [
                'headline_id' => $posts[0]->id,
                'picks' => [
                    $posts[1]->id,
                    $posts[2]->id,
                    $posts[3]->id,
                    $posts[4]->id,
                ],
            ]);
            $res->assertRedirect('/newsroom/kurasi-homepage');
        }
    }

    public function test_04_breaking_news_crud(): void
    {
        $superAdmin = $this->authSuperAdmin();
        $session = [
            'crew_last_activity' => now()->timestamp,
            'email_otp_verified' => true,
        ];

        // List
        $res = $this->actingAs($superAdmin)->withSession($session)->get('/newsroom/breaking-news');
        $res->assertOk();

        // Create
        $res = $this->actingAs($superAdmin)->withSession($session)->post('/newsroom/breaking-news', [
            'title' => 'Audit Test Breaking News '.Str::random(5),
            'url' => 'https://example.com/breaking',
            'duration_hours' => 2,
        ]);
        $res->assertRedirect('/newsroom/breaking-news');

        $bn = BreakingNews::latest('id')->first();
        $this->assertNotNull($bn);

        // Toggle
        $res = $this->actingAs($superAdmin)->withSession($session)->post('/newsroom/breaking-news/'.$bn->id.'/toggle');
        $res->assertRedirect('/newsroom/breaking-news');

        // Delete
        $res = $this->actingAs($superAdmin)->withSession($session)->delete('/newsroom/breaking-news/'.$bn->id);
        $res->assertRedirect('/newsroom/breaking-news');
    }

    public function test_05_category_crud(): void
    {
        $superAdmin = $this->authSuperAdmin();
        $session = [
            'crew_last_activity' => now()->timestamp,
            'email_otp_verified' => true,
        ];

        // List
        $res = $this->actingAs($superAdmin)->withSession($session)->get('/newsroom/kategori');
        $res->assertOk();

        // Create
        $catName = 'Audit Kategori '.Str::random(4);
        $res = $this->actingAs($superAdmin)->withSession($session)->post('/newsroom/kategori', [
            'name' => $catName,
            'badge_color' => '#10B981',
            'sort_order' => 99,
            'is_active' => 1,
        ]);
        $res->assertRedirect('/newsroom/kategori');

        $cat = Category::where('name', $catName)->first();
        $this->assertNotNull($cat);

        // Edit page
        $res = $this->actingAs($superAdmin)->withSession($session)->get('/newsroom/kategori/'.$cat->id.'/edit');
        $res->assertOk();

        // Update
        $res = $this->actingAs($superAdmin)->withSession($session)->put('/newsroom/kategori/'.$cat->id, [
            'name' => $catName.' Updated',
            'badge_color' => '#3B82F6',
            'sort_order' => 100,
            'is_active' => 1,
        ]);
        $res->assertRedirect('/newsroom/kategori');

        // Delete
        $res = $this->actingAs($superAdmin)->withSession($session)->delete('/newsroom/kategori/'.$cat->id);
        $res->assertRedirect('/newsroom/kategori');
    }

    public function test_06_kru_crud(): void
    {
        $superAdmin = $this->authSuperAdmin();
        $session = [
            'crew_last_activity' => now()->timestamp,
            'email_otp_verified' => true,
        ];

        // List
        $res = $this->actingAs($superAdmin)->withSession($session)->get('/newsroom/kru');
        $res->assertOk();

        // Create
        $kruEmail = 'kru.audit.'.Str::random(5).'@portalberita.test';
        $res = $this->actingAs($superAdmin)->withSession($session)->post('/newsroom/kru', [
            'name' => 'Wartawan Audit',
            'email' => $kruEmail,
            'password' => 'password123',
            'role' => RoleName::REPORTER->value,
            'is_active' => 1,
        ]);
        $res->assertRedirect('/newsroom/kru');

        $kru = User::where('email', $kruEmail)->first();
        $this->assertNotNull($kru);
        $this->assertTrue($kru->hasRole(RoleName::REPORTER->value));

        // Edit page
        $res = $this->actingAs($superAdmin)->withSession($session)->get('/newsroom/kru/'.$kru->id.'/edit');
        $res->assertOk();

        // Update
        $res = $this->actingAs($superAdmin)->withSession($session)->put('/newsroom/kru/'.$kru->id, [
            'name' => 'Wartawan Audit Terupdate',
            'email' => $kruEmail,
            'role' => RoleName::DESK_EDITOR->value,
            'is_active' => 1,
        ]);
        $res->assertRedirect('/newsroom/kru');

        $kru->refresh();
        $this->assertTrue($kru->hasRole(RoleName::DESK_EDITOR->value));

        // Delete
        $res = $this->actingAs($superAdmin)->withSession($session)->delete('/newsroom/kru/'.$kru->id);
        $res->assertRedirect('/newsroom/kru');
        $this->assertSoftDeleted('users', ['id' => $kru->id]);
    }

    public function test_07_post_lifecycle(): void
    {
        $superAdmin = $this->authSuperAdmin();
        $category = Category::first();
        $session = [
            'crew_last_activity' => now()->timestamp,
            'email_otp_verified' => true,
        ];

        // 1. Create Post as Draft
        $title = 'Audit Naskah Lengkap '.Str::random(5);
        $res = $this->actingAs($superAdmin)->withSession($session)->post('/newsroom/naskah', [
            'title' => $title,
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan ringkas naskah audit sistem portal berita.',
            'body' => '<p>Paragraf pertama berita audit sistem berita digital presisi dan independen.</p><p>Paragraf kedua berita audit lanjutan investigasi.</p>',
            'action' => 'draft',
            'tags' => 'audit, teknologi, sistem',
        ]);
        $res->assertRedirect('/newsroom/naskah');

        $post = Post::where('title', $title)->first();
        $this->assertNotNull($post);
        $this->assertEquals(PostStatus::DRAFT, $post->status);

        // 2. Edit page
        $res = $this->actingAs($superAdmin)->withSession($session)->get('/newsroom/naskah/'.$post->id.'/sunting');
        $res->assertOk();

        // 3. Submit for review
        $res = $this->actingAs($superAdmin)->withSession($session)->put('/newsroom/naskah/'.$post->id, [
            'title' => $title,
            'category_id' => $category->id,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'action' => 'submit_review',
        ]);
        $res->assertRedirect('/newsroom/naskah');
        $post->refresh();
        $this->assertEquals(PostStatus::UNDER_REVIEW, $post->status);

        // 4. Publish by Super Admin / Editor
        $res = $this->actingAs($superAdmin)->withSession($session)->put('/newsroom/naskah/'.$post->id, [
            'title' => $title,
            'category_id' => $category->id,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'action' => 'publish',
        ]);
        $res->assertRedirect('/newsroom/naskah');
        $post->refresh();
        $this->assertEquals(PostStatus::PUBLISHED, $post->status);

        // 5. Takedown
        $res = $this->actingAs($superAdmin)->withSession($session)->put('/newsroom/naskah/'.$post->id, [
            'title' => $title,
            'category_id' => $category->id,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'action' => 'takedown',
            'takedown_reason' => 'Pengujian takedown komprehensif',
        ]);
        $res->assertRedirect('/newsroom/naskah');
        $post->refresh();
        $this->assertEquals(PostStatus::TAKEDOWN, $post->status);

        // 6. Delete
        $res = $this->actingAs($superAdmin)->withSession($session)->delete('/newsroom/naskah/'.$post->id);
        $res->assertRedirect('/newsroom/naskah');
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_08_interactive_desk_poll_indeks_and_epaper(): void
    {
        // 1. Homepage auto-initializes active Poll and Epaper in DB
        $this->get('/')->assertOk();

        $poll = Poll::currentActive();
        $this->assertNotNull($poll);
        $this->assertGreaterThanOrEqual(3, $poll->options->count());

        // 2. Vote on Survei Pembaca via JSON & verify database persistence
        $firstOption = $poll->options->first();
        $voteRes = $this->postJson('/survei/'.$poll->id.'/vote', [
            'poll_option_id' => $firstOption->id,
        ]);
        $voteRes->assertOk()
            ->assertJsonPath('total_votes', 1)
            ->assertJsonPath('selected_option_id', $firstOption->id);

        $this->assertDatabaseHas('poll_votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $firstOption->id,
        ]);
        $this->assertEquals(1, $firstOption->fresh()->votes_count);

        // 3. Indeks Berita & Arsip Redaksi (/cari) with date and category filters
        $post = Post::published()->first();
        $pubDate = $post->published_at->toDateString();
        $indeksRes = $this->get('/cari?date='.$pubDate.'&kategori='.$post->category->slug);
        $indeksRes->assertOk()->assertSee($post->title);

        // 4. E-Paper Reader (/epaper) & Dynamic PDF Download (/epaper/unduh)
        $epaper = Epaper::currentActive();
        $this->assertNotNull($epaper);
        $initialDownloads = $epaper->downloads_count;

        $this->get('/epaper')->assertOk()->assertSee('RUANG DATA');

        $pdfRes = $this->get('/epaper/unduh');
        $pdfRes->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfRes->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-1.4', (string) $pdfRes->getContent());
        $this->assertEquals($initialDownloads + 1, $epaper->fresh()->downloads_count);

        // 5. Newsroom Interactive Desk Management
        $superAdmin = $this->authSuperAdmin();
        $session = [
            'crew_last_activity' => now()->timestamp,
            'email_otp_verified' => true,
        ];

        $this->actingAs($superAdmin)->withSession($session)
            ->get('/newsroom/interaktif')
            ->assertOk()
            ->assertSee('Meja Interaktif');

        $this->actingAs($superAdmin)->withSession($session)
            ->post('/newsroom/interaktif/survei', [
                'question' => 'Apakah Anda mendukung keterbukaan data publik daerah?',
                'description' => 'Survei transparansi anggaran publik.',
                'edition_label' => 'Edisi Khusus',
                'deadline_label' => 'Minggu depan',
                'options' => ['Sangat Mendukung', 'Cukup Mendukung', 'Kurang Mendukung'],
            ])
            ->assertRedirect('/newsroom/interaktif');

        $this->assertDatabaseHas('polls', [
            'question' => 'Apakah Anda mendukung keterbukaan data publik daerah?',
            'is_active' => true,
        ]);
    }

    public function test_09_reader_avatar_registration_likes_comments_and_admin_moderation(): void
    {
        Storage::fake('public');

        // 1. Pembaca publik daftar akun baru beserta upload foto profil
        $avatarFile = UploadedFile::fake()->image('profil-pembaca.jpg', 300, 300);
        $readerEmail = 'pembaca.aktif@ruangdata.test';

        $regRes = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => $readerEmail,
            'avatar' => $avatarFile,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => '1',
        ]);
        $regRes->assertRedirect();

        $reader = User::where('email', $readerEmail)->first();
        $this->assertNotNull($reader);
        $this->assertFalse($reader->is_crew);
        $this->assertNotNull($reader->avatar_path);
        Storage::disk('public')->assertExists($reader->avatar_path);

        // 2. Pembaca menyukai berita (Like) & membatalkan suka (Unlike)
        $post = Post::published()->first();
        $likeRes = $this->actingAs($reader)->postJson('/interaksi/'.$post->id.'/like');
        $likeRes->assertOk()
            ->assertJsonPath('liked', true)
            ->assertJsonPath('likes_count', 1);

        // 3. Pembaca mengirim komentar pada artikel
        $commentText = 'Analisis data yang sangat tajam dan informatif dari tim redaksi!';
        $commentRes = $this->actingAs($reader)->post('/interaksi/'.$post->id.'/komentar', [
            'body' => $commentText,
        ]);
        $commentRes->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $reader->id,
            'body' => $commentText,
        ]);

        // Pastikan komentar & nama pembaca tampil di halaman detail berita
        $detailRes = $this->get('/'.$post->category->slug.'/'.$post->slug);
        $detailRes->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee($commentText);

        // 4. Super Admin memoderasi / mereset foto profil pembaca yang dianggap tidak pantas
        $superAdmin = $this->authSuperAdmin();
        $session = [
            'crew_last_activity' => now()->timestamp,
            'email_otp_verified' => true,
        ];

        $this->actingAs($superAdmin)->withSession($session)
            ->put('/newsroom/kru/'.$reader->id, [
                'name' => $reader->name,
                'email' => $reader->email,
                'role' => RoleName::READER->value,
                'is_active' => 1,
                'remove_avatar' => 1,
            ])
            ->assertRedirect('/newsroom/kru');

        $this->assertNull($reader->fresh()->avatar_path);
    }
}
