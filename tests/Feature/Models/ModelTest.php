<?php

declare(strict_types=1);

use App\Enums\PermissionName;
use App\Enums\PostStatus;
use App\Enums\RoleName;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCorrection;
use App\Models\PostRevision;
use App\Models\PostStatusHistory;
use App\Models\SlugRedirect;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Seeder RBAC — idempotensi & isi
|--------------------------------------------------------------------------
*/

it('membuat lima peran dan seluruh permission dari enum', function (): void {
    app(RolePermissionSeeder::class)->run();

    expect(Role::count())->toBe(count(RoleName::cases()))
        ->and(Permission::count())->toBe(count(PermissionName::cases()));
});

it('bersifat idempoten saat dijalankan berulang', function (): void {
    app(RolePermissionSeeder::class)->run();
    app(RolePermissionSeeder::class)->run();
    app(RolePermissionSeeder::class)->run();

    expect(Role::count())->toBe(5)
        ->and(Permission::count())->toBe(30)
        ->and(Role::where('name', RoleName::REPORTER->value)->first()->permissions()->count())->toBe(6);
});

it('memberi super-admin seluruh permission', function (): void {
    app(RolePermissionSeeder::class)->run();

    $superAdmin = Role::findByName(RoleName::SUPER_ADMIN->value);

    expect($superAdmin->permissions()->count())->toBe(count(PermissionName::cases()));
});

it('TIDAK memberi reporter permission publishing lewat database', function (): void {
    app(RolePermissionSeeder::class)->run();

    $reporter = Role::findByName(RoleName::REPORTER->value);
    $names = $reporter->permissions()->pluck('name')->all();

    foreach (PermissionName::publishingCapableValues() as $forbidden) {
        expect($names)->not->toContain($forbidden);
    }

    expect($names)->not->toContain(PermissionName::POST_TAKEDOWN->value)
        ->and($names)->not->toContain(PermissionName::HOMEPAGE_CURATE->value);
});

it('memberi permission kepada pengguna melalui peran', function (): void {
    app(RolePermissionSeeder::class)->run();

    $reporter = User::factory()->crew()->withRole(RoleName::REPORTER)->create();

    expect($reporter->hasRole(RoleName::REPORTER->value))->toBeTrue()
        ->and($reporter->can(PermissionName::POST_CREATE->value))->toBeTrue()
        ->and($reporter->can(PermissionName::POST_APPROVE->value))->toBeFalse()
        ->and($reporter->can(PermissionName::POST_TAKEDOWN->value))->toBeFalse();
});

it('memberi pemred kemampuan takedown dan restore', function (): void {
    app(RolePermissionSeeder::class)->run();

    $pemred = User::factory()->crew()->withRole(RoleName::PEMRED)->create();

    expect($pemred->can(PermissionName::POST_TAKEDOWN->value))->toBeTrue()
        ->and($pemred->can(PermissionName::POST_RESTORE->value))->toBeTrue()
        ->and($pemred->can(PermissionName::EDITORIAL_PICKS_MANAGE->value))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Model User
|--------------------------------------------------------------------------
*/

it('menandai kru wajib 2FA dan mendeteksi status 2FA', function (): void {
    $crew = User::factory()->crew()->create();
    $crewWithout = User::factory()->crewWithoutTwoFactor()->create();
    $reader = User::factory()->reader()->create();

    expect($crew->hasTwoFactorEnabled())->toBeTrue()
        ->and($crew->requiresTwoFactor())->toBeTrue()
        ->and($crewWithout->hasTwoFactorEnabled())->toBeFalse()
        ->and($crewWithout->requiresTwoFactor())->toBeTrue()
        ->and($reader->requiresTwoFactor())->toBeFalse();
});

it('menyembunyikan kolom sensitif 2FA saat serialisasi', function (): void {
    $crew = User::factory()->crew()->create();

    $array = $crew->toArray();

    expect($array)->not->toHaveKey('two_factor_secret')
        ->and($array)->not->toHaveKey('two_factor_recovery_codes')
        ->and($array)->not->toHaveKey('password');
});

it('menyediakan scope active, crew, dan readers', function (): void {
    User::factory()->reader()->create();
    User::factory()->crew()->create();
    User::factory()->reader()->inactive()->create();

    expect(User::query()->active()->count())->toBe(2)
        ->and(User::query()->crew()->count())->toBe(1)
        ->and(User::query()->readers()->count())->toBe(2);
});

it('memetakan kategori tugas desk-editor lewat pivot', function (): void {
    $editor = User::factory()->crew()->create();
    $tekno = Category::factory()->create(['name' => 'Tekno', 'slug' => 'tekno']);
    $bola = Category::factory()->create(['name' => 'Bola', 'slug' => 'bola']);

    $editor->assignedCategories()->attach([$tekno->id, $bola->id]);

    expect($editor->assignedCategoryIds())->toHaveCount(2)
        ->and($editor->assignedCategoryIds())->toContain($tekno->id)
        ->and($tekno->editors()->count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Model Category
|--------------------------------------------------------------------------
*/

it('menyimpan badge_color dan memvalidasi format hex', function (): void {
    $valid = Category::factory()->create(['badge_color' => '#B91C1C']);
    $invalid = Category::factory()->create(['badge_color' => 'merah']);

    expect($valid->safeBadgeColor())->toBe('#B91C1C')
        ->and($invalid->safeBadgeColor())->toBe('#334155');
});

it('mengenali slug yang dilarang untuk kategori', function (string $slug): void {
    expect(Category::isReservedSlug($slug))->toBeTrue();
})->with([
    'cari', 'indeks', 'tag', 'penulis', 'redaksi', 'akun', 'admin',
    'newsroom', 'api', 'rss', 'sitemap', 'login', 'register',
    'hak-jawab', 'up', 'storage', 'kebijakan-privasi', 'syarat-ketentuan',
    'pedoman-media-siber',
]);

it('TIDAK menganggap slug biasa sebagai reserved', function (): void {
    expect(Category::isReservedSlug('tekno'))->toBeFalse()
        ->and(Category::isReservedSlug('bola'))->toBeFalse();
});

it('mendeteksi kategori nonaktif yang perlu dialihkan', function (): void {
    $target = Category::factory()->create();
    $redirecting = Category::factory()->redirectingTo($target)->create();
    $plainInactive = Category::factory()->inactive()->create();

    expect($redirecting->shouldRedirect())->toBeTrue()
        ->and($target->shouldRedirect())->toBeFalse()
        ->and($plainInactive->shouldRedirect())->toBeFalse();
});

it('menyediakan scope active, ordered, dan root pada kategori', function (): void {
    $parent = Category::factory()->create(['sort_order' => 1, 'is_active' => true]);
    Category::factory()->create(['sort_order' => 5, 'is_active' => true]);
    Category::factory()->create(['sort_order' => 2, 'is_active' => false]);
    Category::factory()->create(['parent_id' => $parent->id, 'sort_order' => 3, 'is_active' => true]);

    expect(Category::query()->active()->count())->toBe(3)
        ->and(Category::query()->root()->count())->toBe(3)
        ->and(Category::query()->active()->ordered()->first()->sort_order)->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Model Post — FSM
|--------------------------------------------------------------------------
*/

it('me-cast kolom status menjadi enum PostStatus', function (): void {
    $post = Post::factory()->published()->create();

    expect($post->status)->toBeInstanceOf(PostStatus::class)
        ->and($post->status)->toBe(PostStatus::PUBLISHED)
        ->and($post->status->value)->toBe('published');
});

it('memakai default DRAFT dan lock_version 1 untuk artikel baru', function (): void {
    $post = Post::factory()->create();

    // lock_version & views_count dikelola database (kolom internal),
    // jadi nilainya harus dibaca ulang dari baris yang tersimpan.
    $fresh = $post->fresh();

    expect($post->status)->toBe(PostStatus::DRAFT)
        ->and($fresh->lock_version)->toBe(1)
        ->and($fresh->views_count)->toBe(0);
});

it('menentukan transisi yang boleh dilakukan artikel', function (): void {
    $draft = Post::factory()->draft()->create();
    $published = Post::factory()->published()->create();

    expect($draft->canTransitionTo(PostStatus::UNDER_REVIEW))->toBeTrue()
        ->and($draft->canTransitionTo(PostStatus::PUBLISHED))->toBeFalse()
        ->and($published->canTransitionTo(PostStatus::TAKEDOWN))->toBeTrue()
        ->and($published->canTransitionTo(PostStatus::UNDER_REVIEW))->toBeFalse();
});

it('mendeteksi artikel terbit dan artikel terjadwal', function (): void {
    $published = Post::factory()->published()->create();
    $scheduled = Post::factory()->scheduled()->create();
    $draft = Post::factory()->draft()->create();

    expect($published->isPublished())->toBeTrue()
        ->and($scheduled->isPublished())->toBeFalse()
        ->and($draft->isPublished())->toBeFalse();
});

it('menyaring hanya artikel published lewat scope published', function (): void {
    Post::factory()->published()->create();
    Post::factory()->published()->create();
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();
    Post::factory()->takedown()->create();

    expect(Post::query()->published()->count())->toBe(2)
        ->and(Post::query()->count())->toBe(5);
});

it('TIDAK menyertakan artikel terbit di masa depan pada scope published', function (): void {
    Post::factory()->published(now()->addHour())->create();

    expect(Post::query()->published()->count())->toBe(0);
});

it('menyaring artikel berdasarkan status, kategori, dan penulis', function (): void {
    $category = Category::factory()->create();
    $author = User::factory()->crew()->create();

    Post::factory()->published()->create(['category_id' => $category->id, 'author_id' => $author->id]);
    Post::factory()->draft()->create(['category_id' => $category->id, 'author_id' => $author->id]);
    Post::factory()->published()->create();

    expect(Post::query()->status(PostStatus::PUBLISHED)->count())->toBe(2)
        ->and(Post::query()->inCategory($category->id)->count())->toBe(2)
        ->and(Post::query()->authoredBy($author->id)->count())->toBe(2);
});

it('membedakan klaim aktif, klaim kedaluwarsa, dan tanpa klaim', function (): void {
    $editor = User::factory()->crew()->create();

    Post::factory()->underReview()->claimedBy($editor)->create();
    Post::factory()->underReview()->withExpiredClaim($editor)->create();
    Post::factory()->underReview()->create();

    expect(Post::query()->claimActive()->count())->toBe(1)
        ->and(Post::query()->claimExpired()->count())->toBe(1);
});

it('melaporkan status klaim pada instance model', function (): void {
    $editor = User::factory()->crew()->create();

    $active = Post::factory()->underReview()->claimedBy($editor)->create();
    $expired = Post::factory()->underReview()->withExpiredClaim($editor)->create();
    $none = Post::factory()->underReview()->create();

    expect($active->hasActiveClaim())->toBeTrue()
        ->and($active->hasExpiredClaim())->toBeFalse()
        ->and($expired->hasActiveClaim())->toBeFalse()
        ->and($expired->hasExpiredClaim())->toBeTrue()
        ->and($none->hasActiveClaim())->toBeFalse();
});

it('menghitung paragraf bermakna dan mengabaikan paragraf kosong', function (): void {
    $post = Post::factory()->create([
        'body' => '<p>Satu.</p><p>Dua.</p><p>   </p><p>Tiga.</p><p></p><p>Empat.</p>',
    ]);

    expect($post->paragraphCount())->toBe(4);

    $empty = Post::factory()->create(['body' => '<p></p><p>  </p>']);

    expect($empty->paragraphCount())->toBe(0);
});

it('membersihkan tag saat menghitung paragraf berisi hanya markup', function (): void {
    $post = Post::factory()->create(['body' => '<p><strong></strong></p><p>Nyata.</p>']);

    expect($post->paragraphCount())->toBe(1);
});

it('membangun kredit byline reporter dan editor', function (): void {
    $reporter = User::factory()->crew()->create(['name' => 'Budi Santoso']);
    $editor = User::factory()->crew()->create(['name' => 'Sari Dewi']);

    $post = Post::factory()->published()->create([
        'author_id' => $reporter->id,
        'editor_id' => $editor->id,
    ]);

    $draftOnly = Post::factory()->draft()->create(['author_id' => $reporter->id]);

    expect($post->byline())->toBe('Reporter: Budi Santoso | Editor: Sari Dewi')
        ->and($draftOnly->byline())->toBe('Reporter: Budi Santoso');
});

it('membangun URL kanonik dari slug kategori dan artikel', function (): void {
    $category = Category::factory()->create(['slug' => 'tekno']);
    $post = Post::factory()->published()->create([
        'category_id' => $category->id,
        'slug' => 'chip-lokal-naik-kelas-abc123',
    ]);

    expect($post->canonicalPath())->toBe('/tekno/chip-lokal-naik-kelas-abc123');
});

it('memilih meta title dan meta description dengan fallback berurutan', function (): void {
    $post = Post::factory()->published()->create([
        'title' => 'Judul Asli',
        'seo_title' => 'Judul SEO',
        'excerpt' => 'Ringkasan singkat berita.',
        'seo_description' => 'Deskripsi SEO.',
    ]);

    expect($post->metaTitle())->toBe('Judul SEO')
        ->and($post->metaDescription())->toBe('Deskripsi SEO.');

    $fallback = Post::factory()->published()->create([
        'title' => 'Judul Lain',
        'seo_title' => null,
        'seo_description' => null,
        'excerpt' => 'Ringkasan cadangan.',
    ]);

    expect($fallback->metaTitle())->toBe('Judul Lain')
        ->and($fallback->metaDescription())->toBe('Ringkasan cadangan.');
});

it('menyimpan dan mengambil atribusi sumber RSS', function (): void {
    $withSource = Post::factory()->published()->create([
        'source_name' => 'Antara',
        'source_url' => 'https://www.antara.co.id/berita/contoh',
    ]);

    $without = Post::factory()->published()->create();

    expect($withSource->attribution())->toBe('Antara')
        ->and($without->attribution())->toBeNull();
});

it('TIDAK mengizinkan views_count dan lock_version diisi massal', function (): void {
    // Post memakai $guarded sebagai satu-satunya sumber kebenaran, sehingga
    // kolom internal tidak pernah dapat diisi dari input pengguna.
    $post = new Post;

    expect($post->getGuarded())->toContain('views_count')
        ->and($post->getGuarded())->toContain('lock_version')
        ->and($post->getGuarded())->toContain('claimed_by')
        ->and($post->getGuarded())->toContain('claimed_at')
        ->and($post->getGuarded())->toContain('claim_expires_at');

    // Kolom bisnis tetap boleh diisi massal.
    $post->fill(['title' => 'Judul dari Form', 'status' => PostStatus::DRAFT]);

    expect($post->title)->toBe('Judul dari Form')
        ->and($post->status)->toBe(PostStatus::DRAFT)
        ->and($post->views_count)->toBeNull()
        ->and($post->lock_version)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Model pendukung
|--------------------------------------------------------------------------
*/

it('mencatat histori status dengan deskripsi transisi', function (): void {
    $post = Post::factory()->create();
    $actor = User::factory()->crew()->create();

    $history = PostStatusHistory::create([
        'post_id' => $post->id,
        'from_status' => PostStatus::DRAFT->value,
        'to_status' => PostStatus::UNDER_REVIEW->value,
        'actor_id' => $actor->id,
        'reason' => 'Naskah siap direview.',
        'ip_address' => '127.0.0.1',
    ]);

    expect($history->description())->toBe('draft -> under_review')
        ->and($history->post->id)->toBe($post->id)
        ->and($history->actor->id)->toBe($actor->id);
});

it('memberi label baru bila from_status null pada histori', function (): void {
    $post = Post::factory()->create();

    $history = PostStatusHistory::create([
        'post_id' => $post->id,
        'from_status' => null,
        'to_status' => PostStatus::DRAFT->value,
    ]);

    expect($history->description())->toBe('baru -> draft');
});

it('menghapus histori, revisi, dan ralat saat artikel dihapus permanen', function (): void {
    $post = Post::factory()->published()->create();

    PostStatusHistory::create(['post_id' => $post->id, 'from_status' => null, 'to_status' => 'draft']);
    PostRevision::create(['post_id' => $post->id, 'title' => 'v1', 'body' => '<p>a</p>']);
    PostCorrection::create(['post_id' => $post->id, 'summary' => 'Koreksi angka.', 'published_at' => now()]);

    $post->forceDelete();

    expect(PostStatusHistory::where('post_id', $post->id)->count())->toBe(0)
        ->and(PostRevision::where('post_id', $post->id)->count())->toBe(0)
        ->and(PostCorrection::where('post_id', $post->id)->count())->toBe(0);
});

it('menyimpan soft delete pada artikel tanpa menghapus baris', function (): void {
    $post = Post::factory()->published()->create();

    $post->delete();

    expect(Post::query()->count())->toBe(0)
        ->and(Post::withTrashed()->count())->toBe(1);
});

it('memangkas revisi lama sehingga menyisakan batas maksimum', function (): void {
    $post = Post::factory()->create();

    for ($i = 0; $i < 12; $i++) {
        PostRevision::create([
            'post_id' => $post->id,
            'title' => 'versi '.$i,
            'body' => '<p>isi '.$i.'</p>',
            'created_at' => now()->addMinutes($i),
        ]);
    }

    $deleted = PostRevision::pruneForPost($post->id, keep: 5);

    expect($deleted)->toBe(7)
        ->and(PostRevision::where('post_id', $post->id)->count())->toBe(5);
});

it('menyimpan Ralat resmi dan mendeteksi keberadaannya', function (): void {
    $post = Post::factory()->published()->create();
    $actor = User::factory()->crew()->create();

    expect($post->hasCorrections())->toBeFalse();

    PostCorrection::create([
        'post_id' => $post->id,
        'actor_id' => $actor->id,
        'summary' => 'Angka pertumbuhan sebelumnya salah; yang benar 5,2 persen.',
        'published_at' => now(),
    ]);

    expect($post->fresh()->hasCorrections())->toBeTrue()
        ->and($post->corrections()->first()->actor->id)->toBe($actor->id);
});

it('memetakan slug lama ke artikel lewat slug_redirects', function (): void {
    $post = Post::factory()->published()->create();

    $redirect = SlugRedirect::create([
        'old_slug' => 'judul-lama-tanpa-hash',
        'post_id' => $post->id,
    ]);

    expect(SlugRedirect::query()->forSlug('judul-lama-tanpa-hash')->first()->post_id)->toBe($post->id)
        ->and($redirect->post->id)->toBe($post->id);
});

it('menolak slug lama yang duplikat lewat unique constraint', function (): void {
    $post = Post::factory()->published()->create();

    SlugRedirect::create(['old_slug' => 'slug-kembar', 'post_id' => $post->id]);

    expect(static fn () => SlugRedirect::create(['old_slug' => 'slug-kembar', 'post_id' => $post->id]))
        ->toThrow(QueryException::class);
});

it('menghubungkan artikel dengan tag lewat pivot', function (): void {
    $post = Post::factory()->published()->create();
    $tags = Tag::factory()->count(3)->create();

    $post->tags()->attach($tags->pluck('id'));

    expect($post->fresh()->tags)->toHaveCount(3)
        ->and($tags->first()->posts()->count())->toBe(1);
});

it('menghitung tag populer dari artikel published dalam periode', function (): void {
    $popular = Tag::factory()->create();
    $rare = Tag::factory()->create();

    foreach (range(1, 3) as $ignored) {
        $post = Post::factory()->published()->create();
        $post->tags()->attach($popular->id);
    }

    $single = Post::factory()->published()->create();
    $single->tags()->attach($rare->id);

    $result = Tag::query()->popular(7)->limit(2)->get();

    expect($result->first()->id)->toBe($popular->id)
        ->and($result->first()->name)->toBe($popular->name);
});

it('membangun srcset dan rasio aspek pada media', function (): void {
    $media = Media::factory()->create(['width' => 1600, 'height' => 900]);

    expect($media->srcset())->toHaveCount(3)
        ->and($media->srcset())->toHaveKeys(['400w', '800w', '1200w'])
        ->and($media->aspectRatio())->toBe(1.7778)
        ->and($media->srcsetAttribute())->toContain('400w');
});

it('memakai alt_text atau fallback pada media', function (): void {
    $withAlt = Media::factory()->create(['alt_text' => 'Petani memanen padi.']);
    $blankAlt = Media::factory()->create(['alt_text' => '   ']);

    expect($withAlt->altOrFallback('cadangan'))->toBe('Petani memanen padi.')
        ->and($blankAlt->altOrFallback('cadangan'))->toBe('cadangan');
});

it('mengembalikan null rasio aspek bila dimensi tidak diketahui', function (): void {
    $media = Media::factory()->withoutVariants()->create(['width' => null, 'height' => null]);

    expect($media->aspectRatio())->toBeNull()
        ->and($media->variants)->toBeNull();
});
