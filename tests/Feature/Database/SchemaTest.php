<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Skema Database — Fase 1A
|--------------------------------------------------------------------------
|
| Memverifikasi seluruh tabel, kolom kunci, index wajib, engine InnoDB,
| dan collation utf8mb4_unicode_ci. Berjalan di atas database testing.
|
*/

it('membuat seluruh tabel inti portal berita', function (string $table): void {
    expect(Schema::hasTable($table))->toBeTrue();
})->with([
    'users',
    'categories',
    'category_user',
    'media',
    'posts',
    'post_status_histories',
    'post_revisions',
    'post_corrections',
    'tags',
    'post_tag',
    'comments',
    'reactions',
    'bookmarks',
    'breaking_news',
    'editorial_picks',
    'slug_redirects',
    'right_of_reply_requests',
    'activity_log',
]);

it('menambahkan kolom kru dan 2FA pada tabel users', function (string $column): void {
    expect(Schema::hasColumn('users', $column))->toBeTrue();
})->with([
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
    'is_crew',
    'is_active',
    'last_login_at',
    'email_verified_at',
    'deleted_at',
]);

it('mendefinisikan kolom kunci pada tabel posts', function (string $column): void {
    expect(Schema::hasColumn('posts', $column))->toBeTrue();
})->with([
    'category_id',
    'author_id',
    'editor_id',
    'claimed_by',
    'claimed_at',
    'claim_expires_at',
    'featured_media_id',
    'title',
    'slug',
    'excerpt',
    'body',
    'status',
    'editorial_notes',
    'takedown_reason',
    'published_at',
    'views_count',
    'lock_version',
    'seo_title',
    'seo_description',
    'source_url',
    'source_name',
    'deleted_at',
]);

it('TIDAK menggunakan kolom boolean is_published pada posts', function (): void {
    expect(Schema::hasColumn('posts', 'is_published'))->toBeFalse();
});

it('memberi nilai default status draft dan lock_version 1 pada posts', function (): void {
    // Default ditegakkan di level database, bukan hanya di model.
    $status = DB::selectOne('SHOW COLUMNS FROM posts WHERE Field = ?', ['status']);
    $lockVersion = DB::selectOne('SHOW COLUMNS FROM posts WHERE Field = ?', ['lock_version']);
    $viewsCount = DB::selectOne('SHOW COLUMNS FROM posts WHERE Field = ?', ['views_count']);

    expect($status->Default)->toBe(PostStatus::DRAFT->value)
        ->and($lockVersion->Default)->toBe('1')
        ->and($viewsCount->Default)->toBe('0');
});

it('membuat index wajib pada tabel posts', function (string $index): void {
    $indexes = collect(DB::select('SHOW INDEX FROM posts'))->pluck('Key_name')->unique()->all();

    expect($indexes)->toContain($index);
})->with([
    'posts_status_published_at_index',
    'posts_category_status_published_index',
    'posts_author_status_index',
    'posts_claim_index',
    'posts_slug_unique',
    'posts_fulltext_index',
]);

it('membuat index FULLTEXT untuk pencarian default', function (): void {
    $row = DB::selectOne(
        "SELECT INDEX_TYPE FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts'
           AND INDEX_NAME = 'posts_fulltext_index' LIMIT 1"
    );

    expect($row)->not->toBeNull()
        ->and($row->INDEX_TYPE)->toBe('FULLTEXT');
});

it('membuat index wajib pada tabel comments dan tags', function (): void {
    $commentIndexes = collect(DB::select('SHOW INDEX FROM comments'))->pluck('Key_name')->unique()->all();
    $tagIndexes = collect(DB::select('SHOW INDEX FROM tags'))->pluck('Key_name')->unique()->all();

    expect($commentIndexes)->toContain('comments_post_status_created_index')
        ->and($tagIndexes)->toContain('tags_slug_unique');
});

it('menegakkan unique (user_id, post_id) pada reactions dan bookmarks', function (): void {
    $reactionUnique = collect(DB::select('SHOW INDEX FROM reactions'))
        ->firstWhere('Key_name', 'reactions_user_post_unique');
    $bookmarkUnique = collect(DB::select('SHOW INDEX FROM bookmarks'))
        ->firstWhere('Key_name', 'bookmarks_user_post_unique');

    expect($reactionUnique)->not->toBeNull()
        ->and((int) $reactionUnique->Non_unique)->toBe(0)
        ->and($bookmarkUnique)->not->toBeNull()
        ->and((int) $bookmarkUnique->Non_unique)->toBe(0);
});

it('menyediakan tepat lima baris slot editorial picks secara default', function (): void {
    $slots = DB::table('editorial_picks')->pluck('slot')->sort()->values()->all();

    expect($slots)->toBe(['headline', 'pick_1', 'pick_2', 'pick_3', 'pick_4']);
});

it('memakai engine InnoDB dan collation utf8mb4_unicode_ci untuk tabel inti', function (): void {
    $rows = DB::select(
        "SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME IN ('posts','categories','comments','media','users','tags')"
    );

    expect($rows)->toHaveCount(6);

    foreach ($rows as $row) {
        expect($row->ENGINE)->toBe('InnoDB')
            ->and($row->TABLE_COLLATION)->toBe('utf8mb4_unicode_ci');
    }
});

it('menyimpan kolom timestamp sebagai nullable pada posts', function (): void {
    foreach (['published_at', 'claimed_at', 'claim_expires_at'] as $column) {
        $row = DB::selectOne('SHOW COLUMNS FROM posts WHERE Field = ?', [$column]);

        expect($row->Null)->toBe('YES');
    }
});

it('menolak artikel dengan slug duplikat lewat unique constraint database', function (): void {
    $authorId = DB::table('users')->insertGetId([
        'name' => 'Uji Redaksi',
        'email' => 'uji-slug@portal.test',
        'password' => bcrypt('RahasiaPortal2026!'),
        'is_crew' => true,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $categoryId = DB::table('categories')->insertGetId([
        'name' => 'Uji',
        'slug' => 'uji',
        'badge_color' => '#1D4ED8',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $makeRow = static fn (): array => [
        'category_id' => $categoryId,
        'author_id' => $authorId,
        'title' => 'Judul Uji Slug',
        'slug' => 'judul-uji-slug-abc123',
        'status' => PostStatus::DRAFT->value,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('posts')->insert($makeRow());

    expect(static fn () => DB::table('posts')->insert($makeRow()))
        ->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});
