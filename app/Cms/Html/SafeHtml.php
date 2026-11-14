<?php

namespace App\Cms\Html;

/**
 * Membersihkan HTML RichEditor sebelum ditayangkan. Bukan pengganti HTMLPurifier
 * penuh, tetapi menutup vektor umum (script, handler on*, javascript:) dan
 * membatasi iframe serta gambar ke sumber yang diizinkan.
 */
final class SafeHtml
{
    private const ALLOWED = '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote><img><figure><figcaption><span><div><table><thead><tbody><tr><td><th><hr><sup><sub><iframe>';

    private const IFRAME_HOSTS = [
        'www.youtube.com',
        'youtube.com',
        'www.youtube-nocookie.com',
        'player.vimeo.com',
        'www.google.com',
        'maps.google.com',
        'www.google.co.id',
        'maps.google.co.id',
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $cleaned = strip_tags($html, self::ALLOWED);
        $cleaned = preg_replace('/\s(on\w+|xmlns|formaction)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/(javascript|data)\s*:/i', '', $cleaned) ?? $cleaned;
        $cleaned = self::sanitizeImages($cleaned);

        return self::sanitizeIframes($cleaned);
    }

    /**
     * Ambil path disk publik dari src gambar dalam HTML (untuk prune media).
     *
     * @return list<string>
     */
    public static function storagePathsFromHtml(?string $html): array
    {
        if ($html === null || $html === '') {
            return [];
        }

        if (! preg_match_all('/<img\b[^>]*\ssrc\s*=\s*["\']([^"\']+)["\']/i', $html, $matches)) {
            return [];
        }

        $paths = [];
        foreach ($matches[1] as $src) {
            $path = self::storagePathFromSrc(html_entity_decode($src, ENT_QUOTES));
            if ($path !== null) {
                $paths[] = $path;
            }
        }

        return array_values(array_unique($paths));
    }

    private static function sanitizeImages(string $html): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function (array $match): string {
            if (! preg_match('/\ssrc\s*=\s*["\']([^"\']*)["\']/i', $match[0], $src)) {
                return '';
            }

            $url = html_entity_decode($src[1], ENT_QUOTES);
            if (! self::gambarDiizinkan($url)) {
                return '';
            }

            $alt = '';
            if (preg_match('/\salt\s*=\s*["\']([^"\']*)["\']/i', $match[0], $altMatch)) {
                $alt = html_entity_decode($altMatch[1], ENT_QUOTES);
            }

            $attrs = 'src="'.e(self::normalizeImgSrc($url)).'" alt="'.e($alt).'" loading="lazy"';

            if (preg_match('/\swidth\s*=\s*["\']?(\d+(?:\.\d+)?(?:%|px)?)["\']?/i', $match[0], $width)) {
                $attrs .= ' width="'.e($width[1]).'"';
            }

            return '<img '.$attrs.'>';
        }, $html) ?? $html;
    }

    private static function gambarDiizinkan(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        if (preg_match('/^(javascript|data|vbscript)\s*:/i', $url)) {
            return false;
        }

        if (str_starts_with($url, '/storage/')) {
            return ! str_contains($url, '..');
        }

        if (preg_match('#^https?://#i', $url)) {
            $path = (string) parse_url($url, PHP_URL_PATH);

            return str_starts_with($path, '/storage/') && ! str_contains($path, '..');
        }

        return self::storagePathFromSrc($url) !== null;
    }

    private static function normalizeImgSrc(string $url): string
    {
        if (str_starts_with($url, '/storage/')) {
            return $url;
        }

        if (preg_match('#^https?://#i', $url)) {
            $path = (string) parse_url($url, PHP_URL_PATH);

            return $path !== '' ? $path : $url;
        }

        $path = self::storagePathFromSrc($url);

        return $path !== null ? '/storage/'.$path : $url;
    }

    private static function storagePathFromSrc(string $src): ?string
    {
        $src = trim($src);
        if ($src === '') {
            return null;
        }

        $path = $src;
        if (preg_match('#^https?://#i', $src)) {
            $path = (string) parse_url($src, PHP_URL_PATH);
        }

        if (preg_match('#/storage/(.+)$#', $path, $match)) {
            $relative = rawurldecode($match[1]);

            return $relative !== '' && ! str_contains($relative, '..') ? $relative : null;
        }

        if (! str_contains($src, '://') && ! str_starts_with($src, '/') && ! str_contains($src, '..')) {
            return $src;
        }

        return null;
    }

    private static function sanitizeIframes(string $html): string
    {
        return preg_replace_callback('/<iframe\b[^>]*>.*?<\/iframe>/is', function (array $match): string {
            if (! preg_match('/\ssrc\s*=\s*["\']([^"\']+)["\']/i', $match[0], $src)) {
                return '';
            }

            $url = html_entity_decode($src[1], ENT_QUOTES);
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));

            if ($host === '' || ! self::hostDiizinkan($host)) {
                return '';
            }

            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            if (! in_array($scheme, ['https', 'http'], true)) {
                return '';
            }

            return '<iframe src="'.e($url).'" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';
        }, $html) ?? $html;
    }

    private static function hostDiizinkan(string $host): bool
    {
        if (in_array($host, self::IFRAME_HOSTS, true)) {
            return true;
        }

        return str_ends_with($host, '.google.com') || str_ends_with($host, '.google.co.id');
    }
}
