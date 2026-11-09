<?php

declare(strict_types=1);

use App\Enums\CommentStatus;
use App\Enums\EditorialPickSlot;
use App\Enums\PermissionName;
use App\Enums\PostStatus;
use App\Enums\ReactionType;
use App\Enums\RightOfReplyStatus;
use App\Enums\RoleName;
use App\Exceptions\InvalidTransitionException;

/*
|--------------------------------------------------------------------------
| PostStatus — Finite State Machine
|--------------------------------------------------------------------------
*/

it('mendefinisikan seluruh status FSM artikel', function (): void {
    expect(PostStatus::values())->toBe([
        'draft',
        'under_review',
        'rejected',
        'scheduled',
        'published',
        'takedown',
    ]);
});

it('memberi label Bahasa Indonesia untuk setiap status', function (): void {
    expect(PostStatus::DRAFT->label())->toBe('Draf')
        ->and(PostStatus::UNDER_REVIEW->label())->toBe('Menunggu Review')
        ->and(PostStatus::REJECTED->label())->toBe('Ditolak')
        ->and(PostStatus::SCHEDULED->label())->toBe('Terjadwal')
        ->and(PostStatus::PUBLISHED->label())->toBe('Terbit')
        ->and(PostStatus::TAKEDOWN->label())->toBe('Diturunkan');
});

it('mengizinkan transisi yang terdaftar pada matriks', function (string $from, string $to): void {
    expect(PostStatus::from($from)->canTransitionTo(PostStatus::from($to)))->toBeTrue();
})->with([
    ['draft', 'under_review'],
    ['under_review', 'draft'],
    ['under_review', 'rejected'],
    ['under_review', 'scheduled'],
    ['under_review', 'published'],
    ['rejected', 'under_review'],
    ['scheduled', 'published'],
    ['scheduled', 'under_review'],
    ['published', 'takedown'],
    ['takedown', 'published'],
]);

it('menolak transisi di luar matriks', function (string $from, string $to): void {
    expect(PostStatus::from($from)->canTransitionTo(PostStatus::from($to)))->toBeFalse();
})->with([
    // Reporter tidak boleh melompati review
    ['draft', 'published'],
    ['draft', 'scheduled'],
    ['draft', 'rejected'],
    ['rejected', 'published'],
    // Tidak ada jalan langsung dari published
    ['published', 'draft'],
    ['published', 'under_review'],
    ['published', 'scheduled'],
    // Takedown tidak kembali ke draft
    ['takedown', 'draft'],
    ['takedown', 'under_review'],
    // Tidak ada transisi ke diri sendiri
    ['draft', 'draft'],
    ['published', 'published'],
    ['takedown', 'takedown'],
    // Scheduled tidak langsung ke takedown
    ['scheduled', 'takedown'],
]);

it('hanya menganggap published sebagai status yang terlihat publik', function (): void {
    expect(PostStatus::PUBLISHED->isPubliclyVisible())->toBeTrue()
        ->and(PostStatus::DRAFT->isPubliclyVisible())->toBeFalse()
        ->and(PostStatus::SCHEDULED->isPubliclyVisible())->toBeFalse()
        ->and(PostStatus::TAKEDOWN->isPubliclyVisible())->toBeFalse();
});

it('mengunci slug hanya pada status published dan takedown', function (): void {
    expect(PostStatus::PUBLISHED->locksSlug())->toBeTrue()
        ->and(PostStatus::TAKEDOWN->locksSlug())->toBeTrue()
        ->and(PostStatus::DRAFT->locksSlug())->toBeFalse()
        ->and(PostStatus::SCHEDULED->locksSlug())->toBeFalse();
});

it('mengizinkan reporter menyunting hanya pada draft dan rejected', function (): void {
    expect(PostStatus::DRAFT->isEditableByReporter())->toBeTrue()
        ->and(PostStatus::REJECTED->isEditableByReporter())->toBeTrue()
        ->and(PostStatus::UNDER_REVIEW->isEditableByReporter())->toBeFalse()
        ->and(PostStatus::PUBLISHED->isEditableByReporter())->toBeFalse();
});

it('menyediakan daftar status yang terlihat publik untuk query', function (): void {
    expect(PostStatus::publiclyVisibleValues())->toBe(['published']);
});

/*
|--------------------------------------------------------------------------
| InvalidTransitionException
|--------------------------------------------------------------------------
*/

it('membangun pesan exception transisi yang informatif', function (): void {
    $exception = new InvalidTransitionException(PostStatus::DRAFT, PostStatus::PUBLISHED, 42);

    expect($exception->getMessage())->toContain('#42')
        ->and($exception->getMessage())->toContain('draft -> published')
        ->and($exception->from())->toBe(PostStatus::DRAFT)
        ->and($exception->to())->toBe(PostStatus::PUBLISHED);
});

/*
|--------------------------------------------------------------------------
| RoleName & PermissionName — RBAC 5-Tier
|--------------------------------------------------------------------------
*/

it('mendefinisikan lima peran RBAC tanpa magic string', function (): void {
    expect(RoleName::values())->toBe([
        'super-admin',
        'pemred',
        'desk-editor',
        'reporter',
        'reader',
    ]);
});

it('menandai seluruh peran kecuali reader sebagai kru', function (): void {
    expect(RoleName::SUPER_ADMIN->isCrew())->toBeTrue()
        ->and(RoleName::PEMRED->isCrew())->toBeTrue()
        ->and(RoleName::DESK_EDITOR->isCrew())->toBeTrue()
        ->and(RoleName::REPORTER->isCrew())->toBeTrue()
        ->and(RoleName::READER->isCrew())->toBeFalse();
});

it('TIDAK memberi reporter permission yang dapat memicu SCHEDULED atau PUBLISHED', function (): void {
    $reporterPermissions = array_map(
        static fn (PermissionName $p): string => $p->value,
        PermissionName::forReporter(),
    );

    foreach (PermissionName::publishingCapableValues() as $forbidden) {
        expect($reporterPermissions)->not->toContain($forbidden);
    }
});

it('TIDAK memberi reporter akses ke permission kurasi homepage', function (): void {
    $reporterPermissions = array_map(
        static fn (PermissionName $p): string => $p->value,
        PermissionName::forReporter(),
    );

    expect($reporterPermissions)->not->toContain(PermissionName::HOMEPAGE_CURATE->value)
        ->and($reporterPermissions)->not->toContain(PermissionName::EDITORIAL_PICKS_MANAGE->value)
        ->and($reporterPermissions)->not->toContain(PermissionName::POST_TAKEDOWN->value)
        ->and($reporterPermissions)->not->toContain(PermissionName::POST_FORCE_CLAIM->value);
});

it('memberi pemred permission takedown, restore, dan kurasi', function (): void {
    $pemred = array_map(
        static fn (PermissionName $p): string => $p->value,
        PermissionName::forPemred(),
    );

    expect($pemred)->toContain('post.takedown')
        ->and($pemred)->toContain('post.restore')
        ->and($pemred)->toContain('homepage.curate')
        ->and($pemred)->toContain('breaking-news.manage')
        ->and($pemred)->toContain('editorial-picks.manage')
        ->and($pemred)->toContain('correction.publish')
        ->and($pemred)->toContain('rightofreply.handle')
        ->and($pemred)->toContain('post.force-claim');
});

it('memberi desk-editor permission review, claim, approve, dan reject', function (): void {
    $deskEditor = array_map(
        static fn (PermissionName $p): string => $p->value,
        PermissionName::forDeskEditor(),
    );

    expect($deskEditor)->toContain('post.review')
        ->and($deskEditor)->toContain('post.claim')
        ->and($deskEditor)->toContain('post.approve')
        ->and($deskEditor)->toContain('post.reject')
        ->and($deskEditor)->toContain('post.schedule')
        ->and($deskEditor)->toContain('post.minor-edit')
        ->and($deskEditor)->toContain('post.edit-in-desk');
});

it('memberi reader hanya permission komentar, reaksi, dan bookmark', function (): void {
    $reader = array_map(
        static fn (PermissionName $p): string => $p->value,
        PermissionName::forReader(),
    );

    expect($reader)->toBe(['comment.create', 'reaction.create', 'bookmark.manage']);
});

it('TIDAK memberi desk-editor permission takedown milik pemred', function (): void {
    $deskEditor = array_map(
        static fn (PermissionName $p): string => $p->value,
        PermissionName::forDeskEditor(),
    );

    expect($deskEditor)->not->toContain(PermissionName::POST_TAKEDOWN->value)
        ->and($deskEditor)->not->toContain(PermissionName::POST_FORCE_CLAIM->value)
        ->and($deskEditor)->not->toContain(PermissionName::CORRECTION_PUBLISH->value);
});

it('memetakan setiap peran ke daftar permission-nya', function (): void {
    $map = PermissionName::byRole();

    expect(array_keys($map))->toBe(RoleName::values())
        ->and($map['reader'])->toHaveCount(3)
        ->and($map['reporter'])->toHaveCount(6);
});

it('mendefinisikan setiap nilai permission tepat satu kali', function (): void {
    $all = array_map(
        static fn (PermissionName $permission): string => $permission->value,
        PermissionName::cases(),
    );

    expect($all)->toBe(array_unique($all))
        ->and($all)->toHaveCount(count(PermissionName::cases()));
});

it('hanya memakai permission yang terdaftar pada enum di peta peran', function (): void {
    $registered = PermissionName::values();

    foreach (PermissionName::byRole() as $role => $permissions) {
        foreach ($permissions as $permission) {
            expect($registered)->toContain($permission->value);
        }
    }
});

/*
|--------------------------------------------------------------------------
| Enum pendukung
|--------------------------------------------------------------------------
*/

it('mendefinisikan empat jenis reaksi dengan emoji', function (): void {
    expect(ReactionType::values())->toBe(['like', 'surprised', 'sad', 'angry'])
        ->and(ReactionType::LIKE->emoji())->not->toBeEmpty()
        ->and(ReactionType::options())->toHaveCount(4);
});

it('mendefinisikan empat status moderasi komentar', function (): void {
    expect(CommentStatus::values())->toBe(['pending', 'approved', 'spam', 'rejected'])
        ->and(CommentStatus::APPROVED->isPubliclyVisible())->toBeTrue()
        ->and(CommentStatus::PENDING->isPubliclyVisible())->toBeFalse()
        ->and(CommentStatus::SPAM->isModeratable())->toBeFalse();
});

it('mendefinisikan lima slot editorial picks dengan urutan render', function (): void {
    expect(EditorialPickSlot::values())->toBe(['headline', 'pick_1', 'pick_2', 'pick_3', 'pick_4'])
        ->and(EditorialPickSlot::HEADLINE->isHeadline())->toBeTrue()
        ->and(EditorialPickSlot::HEADLINE->sortOrder())->toBe(1)
        ->and(EditorialPickSlot::picks())->toHaveCount(4)
        ->and(EditorialPickSlot::PICK_1->isHeadline())->toBeFalse();
});

it('mendefinisikan empat status permohonan hak jawab', function (): void {
    expect(RightOfReplyStatus::values())->toBe(['pending', 'reviewed', 'published', 'rejected'])
        ->and(RightOfReplyStatus::PUBLISHED->isClosed())->toBeTrue()
        ->and(RightOfReplyStatus::REJECTED->isClosed())->toBeTrue()
        ->and(RightOfReplyStatus::PENDING->isClosed())->toBeFalse();
});
