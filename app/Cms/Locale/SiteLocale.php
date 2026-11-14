<?php

namespace App\Cms\Locale;

/**
 * Locale situs publik. Hanya id/en; EN opsional per konten.
 */
final class SiteLocale
{
    public const SESSION_KEY = 'site_locale';

    public const SUPPORTED = ['id', 'en'];

    public static function current(): string
    {
        $locale = session(self::SESSION_KEY, config('app.locale', 'id'));

        return in_array($locale, self::SUPPORTED, true) ? $locale : 'id';
    }

    public static function set(string $locale): void
    {
        if (! in_array($locale, self::SUPPORTED, true)) {
            return;
        }

        session([self::SESSION_KEY => $locale]);
        app()->setLocale($locale);
    }
}
