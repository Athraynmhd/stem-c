<?php

namespace App\Cms\Access;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Matriks PDF §5.1:
 * Editor = profil diri + publikasi/riset draft, tanpa berita/kegiatan/galeri, tanpa publish.
 * Admin konten = seluruh konten publik + audit terbatas, tanpa user/sistem.
 * Super admin = penuh.
 */
final class CmsAccess
{
    public const NEWS = 'news';

    public const EVENTS = 'events';

    public const MEDIA = 'media';

    public const PAGES = 'pages';

    public const PUBLICATIONS = 'publications';

    public const DOCUMENTS = 'documents';

    public const RESEARCHERS = 'researchers';

    public const RESEARCH = 'research';

    public const RESEARCH_GROUPS = 'research_groups';

    public const SETTINGS = 'settings';

    public const CONTACT = 'contact';

    public const SITE_CHROME = 'site_chrome';

    public const HOMEPAGE = 'homepage';

    public const USERS = 'users';

    public const AUDIT = 'audit';

    public const MEDIA_LIBRARY = 'media_library';

    public const WORKFLOW_DRAFT = 'draft';

    public const WORKFLOW_REVIEW = 'review';

    public const WORKFLOW_PUBLISHED = 'published';

    /** Editor: profil diri, publikasi & riset (draft/review), tanpa rilis harian. */
    private const EDITOR = [
        self::RESEARCHERS,
        self::PUBLICATIONS,
        self::RESEARCH,
        self::RESEARCH_GROUPS,
    ];

    private const ADMIN_KONTEN = [
        self::NEWS,
        self::EVENTS,
        self::MEDIA,
        self::PAGES,
        self::PUBLICATIONS,
        self::DOCUMENTS,
        self::RESEARCHERS,
        self::RESEARCH,
        self::RESEARCH_GROUPS,
        self::CONTACT,
        self::SITE_CHROME,
        self::HOMEPAGE,
        self::MEDIA_LIBRARY,
        self::AUDIT,
    ];

    public static function can(?User $user, string $module): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin_konten')) {
            return in_array($module, self::ADMIN_KONTEN, true);
        }

        if ($user->hasRole('editor')) {
            return in_array($module, self::EDITOR, true);
        }

        return false;
    }

    public static function canPublish(?User $user): bool
    {
        return $user !== null && $user->hasAnyRole(['super_admin', 'admin_konten']);
    }

    public static function canManageUsers(?User $user): bool
    {
        return $user !== null && $user->hasRole('super_admin');
    }

    public static function owns(User $user, Model $record): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin_konten'])) {
            return true;
        }

        if ($record->getAttribute('user_id') === $user->getKey()) {
            return true;
        }

        if ($record->getAttribute('created_by') === $user->getKey()) {
            return true;
        }

        return false;
    }

    public static function workflowOptions(?User $user): array
    {
        $options = [
            self::WORKFLOW_DRAFT => 'Draft',
            self::WORKFLOW_REVIEW => 'Ditinjau',
        ];

        if (self::canPublish($user)) {
            $options[self::WORKFLOW_PUBLISHED] = 'Tayang';
        }

        return $options;
    }
}
