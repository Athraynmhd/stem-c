<?php

namespace App\Cms\Navigation;

use App\Models\Content\Document;
use App\Models\Content\Page;
use App\Models\Content\ResearchGroup;
use Illuminate\Support\Facades\Cache;

class SiteMenu
{
    public const CACHE_KEY = 'site_menu.structure';

    public const CACHE_TTL_SECONDS = 600;

    public static function build(): array
    {
        $struktur = Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn () => self::struktur(),
        );

        return self::terapkanAktif($struktur);
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('stemc.public_stats');
    }

    private static function struktur(): array
    {
        return [
            self::butir(__('nav.home'), route('home')),

            self::butir(
                __('nav.about'),
                route('profil'),
                self::anakProfil(),
            ),

            self::butir(__('nav.researchers'), route('peneliti.index'), [
                self::butir(__('nav.researcher_directory'), route('peneliti.index')),
                ...self::anakKelompok(),
            ]),

            self::butir(__('nav.research'), route('riset.index'), [
                self::butir(__('nav.catalog'), route('riset.index')),
                self::butir(__('nav.publications'), route('publikasi.index')),
                self::butir(__('nav.products'), route('riset.produk')),
                self::butir(__('nav.hki'), route('riset.hki')),
                self::butir(__('nav.modules'), route('dokumen.index', ['kategori' => 'materi'])),
            ]),

            self::butir(__('nav.news_events'), route('kegiatan.index'), [
                self::butir(__('nav.news'), route('berita.index')),
                self::butir(__('nav.agenda'), route('kegiatan.index')),
                self::butir(__('nav.calendar'), route('kegiatan.kalender')),
                self::butir(__('nav.gallery'), route('dokumentasi.index')),
                self::butir(__('nav.call'), route('kegiatan.call')),
            ]),

            self::butir(__('nav.partnership'), route('kerja-sama.mitra'), [
                self::butir(__('nav.partners'), route('kerja-sama.mitra')),
                self::butir(__('nav.mou'), route('kerja-sama.mou')),
                self::butir(__('nav.apply'), route('kerja-sama.ajukan')),
            ]),

            self::butir(__('nav.documents'), route('dokumen.index'), self::anakDokumen()),

            self::butir(__('nav.contact'), route('kontak')),
        ];
    }

    private static function terapkanAktif(array $menu): array
    {
        $slugAktif = request()->route('slug');
        $kategoriAktif = request()->query('kategori');
        $jenisAktif = request()->query('jenis');

        return array_map(function (array $butir) use ($slugAktif, $kategoriAktif) {
            $label = $butir['label'];
            $key = $butir['key'] ?? $label;

            $aktif = match (true) {
                request()->routeIs('home') && str_contains($butir['url'], rtrim(route('home'), '/')) && $butir['url'] === route('home')
                    && $label === __('nav.home') => true,
                $label === __('nav.home') => request()->routeIs('home'),
                $label === __('nav.about') => request()->routeIs('profil', 'profil.detail'),
                $label === __('nav.researchers') => request()->routeIs('peneliti.*', 'kelompok.*'),
                $label === __('nav.research') => request()->routeIs('riset.*', 'publikasi.*', 'dokumen.index') && request()->query('kategori') === 'materi',
                $label === __('nav.news_events') => request()->routeIs('kegiatan.*', 'berita.*', 'dokumentasi.index'),
                $label === __('nav.partnership') => request()->routeIs('kerja-sama.*'),
                $label === __('nav.documents') => request()->routeIs('dokumen.index') && request()->query('kategori') !== 'materi',
                $label === __('nav.contact') => request()->routeIs('kontak', 'privasi'),
                default => false,
            };

            if ($label === __('nav.research') && request()->routeIs('riset.*', 'publikasi.*')) {
                $aktif = true;
            }

            $anak = array_map(function (array $child) use ($label, $slugAktif, $kategoriAktif) {
                $childAktif = match ($label) {
                    __('nav.about') => request()->routeIs('profil.detail') && $slugAktif === self::slugDariUrl($child['url']),
                    __('nav.researchers') => request()->fullUrlIs($child['url']) || request()->url() === $child['url'],
                    __('nav.research') => request()->fullUrlIs($child['url']) || request()->url() === strtok($child['url'], '?'),
                    __('nav.news_events') => request()->fullUrlIs($child['url']) || request()->url() === $child['url'],
                    __('nav.partnership') => request()->url() === $child['url'],
                    __('nav.documents') => request()->routeIs('dokumen.index')
                        && $kategoriAktif === self::queryDariUrl($child['url'], 'kategori'),
                    default => false,
                };

                return [
                    ...$child,
                    'aktif' => $childAktif,
                    'anak' => $child['anak'] ?? [],
                ];
            }, $butir['anak'] ?? []);

            return [
                ...$butir,
                'aktif' => $aktif,
                'anak' => $anak,
            ];
        }, $menu);
    }

    private static function slugDariUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $segmen = array_values(array_filter(explode('/', $path)));

        return $segmen === [] ? null : end($segmen);
    }

    private static function queryDariUrl(string $url, string $key): ?string
    {
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $nilai = $query[$key] ?? null;

        return is_string($nilai) ? $nilai : null;
    }

    private static function butir(string $label, string $url, array $anak = []): array
    {
        return [
            'label' => $label,
            'url' => $url,
            'anak' => $anak,
        ];
    }

    private static function anakProfil(): array
    {
        return Page::query()
            ->published()
            ->orderBy('sort_order')
            ->get(['title', 'title_en', 'slug'])
            ->map(fn (Page $page) => self::butir(
                (string) $page->localized('title'),
                route('profil.detail', $page->slug),
            ))
            ->all();
    }

    private static function anakKelompok(): array
    {
        return ResearchGroup::query()
            ->published()
            ->orderBy('sort_order')
            ->get(['name', 'name_en', 'slug'])
            ->map(fn (ResearchGroup $group) => self::butir(
                (string) $group->localized('name'),
                route('kelompok.show', $group->slug),
            ))
            ->all();
    }

    private static function anakDokumen(): array
    {
        $terpakai = Document::query()->published()->distinct()->pluck('category')->all();

        return collect(Document::CATEGORIES)
            ->only($terpakai)
            ->map(fn (string $label, string $nilai) => self::butir(
                $label,
                route('dokumen.index', ['kategori' => $nilai]),
            ))
            ->values()
            ->all();
    }
}
