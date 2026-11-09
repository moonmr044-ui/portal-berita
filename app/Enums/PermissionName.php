<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Nama permission RBAC.
 *
 * Seluruh pengecekan wewenang memakai Enum ini melalui Policy/Gate berbasis
 * permission. Dilarang memeriksa nama role secara langsung di controller.
 */
enum PermissionName: string
{
    // --- Artikel: pemred ---
    case POST_TAKEDOWN = 'post.takedown';
    case POST_RESTORE = 'post.restore';
    case POST_FORCE_CLAIM = 'post.force-claim';

    // --- Kurasi homepage ---
    case HOMEPAGE_CURATE = 'homepage.curate';
    case BREAKING_NEWS_MANAGE = 'breaking-news.manage';
    case EDITORIAL_PICKS_MANAGE = 'editorial-picks.manage';

    // --- Artikel: desk editor ---
    case POST_REVIEW = 'post.review';
    case POST_CLAIM = 'post.claim';
    case POST_APPROVE = 'post.approve';
    case POST_REJECT = 'post.reject';
    case POST_SCHEDULE = 'post.schedule';
    case POST_MINOR_EDIT = 'post.minor-edit';
    case POST_EDIT_IN_DESK = 'post.edit-in-desk';

    // --- Artikel: reporter ---
    case POST_CREATE = 'post.create';
    case POST_EDIT_OWN = 'post.edit-own';
    case POST_SUBMIT = 'post.submit';
    case POST_WITHDRAW = 'post.withdraw';
    case MEDIA_UPLOAD = 'media.upload';

    // --- Artikel: baca ---
    case POST_VIEW_ANY = 'post.view-any';

    // --- Redaksi ---
    case CORRECTION_PUBLISH = 'correction.publish';
    case RIGHT_OF_REPLY_HANDLE = 'rightofreply.handle';

    // --- Pembaca ---
    case COMMENT_CREATE = 'comment.create';
    case COMMENT_MODERATE = 'comment.moderate';
    case REACTION_CREATE = 'reaction.create';
    case BOOKMARK_MANAGE = 'bookmark.manage';

    // --- Super admin ---
    case CREW_MANAGE = 'crew.manage';
    case CATEGORY_MANAGE = 'category.manage';
    case AUDIT_LOG_VIEW = 'audit-log.view';
    case BACKUP_RUN = 'backup.run';
    case ANALYTICS_VIEW = 'analytics.view';

    /**
     * Permission yang diberikan kepada peran pemred.
     *
     * @return list<self>
     */
    public static function forPemred(): array
    {
        return [
            self::POST_TAKEDOWN,
            self::POST_RESTORE,
            self::POST_FORCE_CLAIM,
            self::HOMEPAGE_CURATE,
            self::BREAKING_NEWS_MANAGE,
            self::EDITORIAL_PICKS_MANAGE,
            self::POST_VIEW_ANY,
            self::CORRECTION_PUBLISH,
            self::RIGHT_OF_REPLY_HANDLE,
            self::COMMENT_MODERATE,
            self::ANALYTICS_VIEW,
        ];
    }

    /**
     * Permission yang diberikan kepada peran desk-editor.
     * Cakupan kategori dibatasi melalui pivot `category_user`.
     *
     * @return list<self>
     */
    public static function forDeskEditor(): array
    {
        return [
            self::POST_REVIEW,
            self::POST_CLAIM,
            self::POST_APPROVE,
            self::POST_REJECT,
            self::POST_SCHEDULE,
            self::POST_MINOR_EDIT,
            self::POST_EDIT_IN_DESK,
            self::POST_VIEW_ANY,
            self::MEDIA_UPLOAD,
            self::COMMENT_MODERATE,
        ];
    }

    /**
     * Permission yang diberikan kepada peran reporter.
     *
     * Reporter TIDAK PERNAH memiliki permission yang dapat memicu status
     * SCHEDULED atau PUBLISHED. Aturan ini diuji dengan automated test.
     *
     * @return list<self>
     */
    public static function forReporter(): array
    {
        return [
            self::POST_CREATE,
            self::POST_EDIT_OWN,
            self::POST_SUBMIT,
            self::POST_WITHDRAW,
            self::MEDIA_UPLOAD,
            self::POST_VIEW_ANY,
        ];
    }

    /**
     * Permission yang diberikan kepada peran reader.
     * Wajib sudah terverifikasi email.
     *
     * @return list<self>
     */
    public static function forReader(): array
    {
        return [
            self::COMMENT_CREATE,
            self::REACTION_CREATE,
            self::BOOKMARK_MANAGE,
        ];
    }

    /**
     * Permission eksklusif super-admin.
     *
     * @return list<self>
     */
    public static function forSuperAdmin(): array
    {
        return [
            self::CREW_MANAGE,
            self::CATEGORY_MANAGE,
            self::AUDIT_LOG_VIEW,
            self::BACKUP_RUN,
            self::ANALYTICS_VIEW,
            self::POST_VIEW_ANY,
        ];
    }

    /**
     * Peta permission => peran pemiliknya, dipakai RolePermissionSeeder.
     *
     * @return array<string, list<self>>
     */
    public static function byRole(): array
    {
        return [
            RoleName::SUPER_ADMIN->value => self::forSuperAdmin(),
            RoleName::PEMRED->value => self::forPemred(),
            RoleName::DESK_EDITOR->value => self::forDeskEditor(),
            RoleName::REPORTER->value => self::forReporter(),
            RoleName::READER->value => self::forReader(),
        ];
    }

    /**
     * Permission yang dapat memicu status SCHEDULED atau PUBLISHED.
     * Dipakai test untuk memastikan reporter tidak memilikinya.
     *
     * @return list<string>
     */
    public static function publishingCapableValues(): array
    {
        return [
            self::POST_APPROVE->value,
            self::POST_SCHEDULE->value,
            self::POST_FORCE_CLAIM->value,
        ];
    }

    /**
     * Semua nilai permission sebagai array string.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
