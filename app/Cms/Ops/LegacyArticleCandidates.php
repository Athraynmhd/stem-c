<?php

namespace App\Cms\Ops;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Daftar judul+URL dari situs WordPress lama untuk kurasi manual.
 * Tidak menulis ke basis data.
 */
final class LegacyArticleCandidates
{
    /**
     * @return list<array{title: string, url: string, suspect: bool, reason: ?string}>
     */
    public static function daftar(bool $segar = false): array
    {
        $menit = max(15, (int) config('cms.legacy_source.cache_minutes', 180));
        $kunci = 'stemc.legacy_article_candidates';

        if ($segar) {
            Cache::forget($kunci);
        }

        return Cache::remember($kunci, now()->addMinutes($menit), fn () => self::ambil());
    }

    /**
     * @return list<array{title: string, url: string, suspect: bool, reason: ?string}>
     */
    private static function ambil(): array
    {
        $dasar = rtrim((string) config('cms.legacy_source.base_url', 'https://stem.lppm.usk.ac.id'), '/');
        $halaman = max(1, (int) config('cms.legacy_source.pages', 6));
        $ditemukan = [];

        for ($i = 1; $i <= $halaman; $i++) {
            $url = $i === 1 ? $dasar.'/' : $dasar.'/page/'.$i.'/';
            $html = self::unduh($url);

            if ($html === null) {
                continue;
            }

            foreach (self::ekstrak($html, $dasar) as $item) {
                $ditemukan[$item['url']] = $item;
            }
        }

        $hasil = array_values($ditemukan);
        usort($hasil, fn (array $a, array $b) => strcmp($a['title'], $b['title']));

        return $hasil;
    }

    private static function unduh(string $url): ?string
    {
        try {
            $respon = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'STEM-C-USK-kurasi/1.0'])
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $respon->successful()) {
            return null;
        }

        return $respon->body();
    }

    /**
     * @return list<array{title: string, url: string, suspect: bool, reason: ?string}>
     */
    private static function ekstrak(string $html, string $dasar): array
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);
        $host = parse_url($dasar, PHP_URL_HOST) ?: 'stem.lppm.usk.ac.id';
        $items = [];

        foreach ($xpath->query('//a[@href]') as $tautan) {
            if (! $tautan instanceof \DOMElement) {
                continue;
            }

            $href = trim($tautan->getAttribute('href'));
            $judul = trim(preg_replace('/\s+/', ' ', $tautan->textContent) ?? '');

            if ($judul === '' || mb_strlen($judul) < 12) {
                continue;
            }

            $absolut = self::absolut($href, $dasar);
            if ($absolut === null) {
                continue;
            }

            $hostTautan = parse_url($absolut, PHP_URL_HOST);
            if ($hostTautan !== $host) {
                continue;
            }

            $path = parse_url($absolut, PHP_URL_PATH) ?? '/';
            if (! self::miripArtikel($path, $absolut)) {
                continue;
            }

            $curiga = self::curiga($judul.' '.$absolut);
            $items[$absolut] = [
                'title' => Str::limit($judul, 180),
                'url' => $absolut,
                'suspect' => $curiga !== null,
                'reason' => $curiga,
            ];
        }

        return array_values($items);
    }

    private static function miripArtikel(string $path, string $url): bool
    {
        if (preg_match('#/\d{4}/\d{2}/.+#', $path) === 1) {
            return true;
        }

        if (str_contains($url, '?p=')) {
            return true;
        }

        $buang = ['/page/', '/category/', '/tag/', '/author/', '/wp-admin', '/wp-json', '/feed', '/wp-content'];
        foreach ($buang as $potong) {
            if (str_contains(strtolower($path), $potong)) {
                return false;
            }
        }

        $slug = trim($path, '/');

        return $slug !== '' && ! str_contains($slug, '/') && mb_strlen($slug) > 8;
    }

    private static function curiga(string $teks): ?string
    {
        $rendah = mb_strtolower($teks);
        $pola = [
            'kmspico' => 'spam aktivator',
            'activator' => 'spam aktivator',
            'crack' => 'indikasi crack',
            'bajakan' => 'indikasi bajakan',
            'casino' => 'spam judi',
            'judi' => 'spam judi',
            'slot gacor' => 'spam judi',
            'viagra' => 'spam',
            'license key' => 'spam lisensi',
            'office 2019' => 'spam office',
        ];

        foreach ($pola as $kata => $alasan) {
            if (str_contains($rendah, $kata)) {
                return $alasan;
            }
        }

        return null;
    }

    private static function absolut(string $href, string $dasar): ?string
    {
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:')) {
            return null;
        }

        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return strtok($href, '#') ?: null;
        }

        if (str_starts_with($href, '//')) {
            return 'https:'.strtok($href, '#');
        }

        if (str_starts_with($href, '/')) {
            $parts = parse_url($dasar);

            return ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').strtok($href, '#');
        }

        return rtrim($dasar, '/').'/'.ltrim(strtok($href, '#') ?: '', '/');
    }
}
