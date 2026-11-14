<?php

namespace App\Cms\Chrome;

use App\Models\Chrome\Partner;
use App\Models\Chrome\SiteSection;
use App\Models\Chrome\SiteSlide;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Akses terpusat ke chrome situs (section, slide, mitra).
 * Memoisasi per-request; sengaja tidak menyimpan Eloquent ke cache driver
 * database karena unserialize model sering gagal.
 */
final class SiteContent
{
    /** @var array{sections: Collection, slides: Collection, partners: Collection}|null */
    private static ?array $memo = null;

    public static function section(string $key, bool $onlyPublished = true): ?SiteSection
    {
        $section = static::payload()['sections']->get($key);

        if ($section === null) {
            return null;
        }

        if ($onlyPublished && ! $section->is_published) {
            return null;
        }

        return $section;
    }

    /**
     * @return Collection<int, SiteSlide>
     */
    public static function slides(string $placement, bool $onlyPublished = true): Collection
    {
        return static::payload()['slides']
            ->where('placement', $placement)
            ->when($onlyPublished, fn (Collection $items) => $items->where('is_published', true))
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * @return Collection<int, Partner>
     */
    public static function partners(bool $onlyPublished = true): Collection
    {
        return static::payload()['partners']
            ->when($onlyPublished, fn (Collection $items) => $items->where('is_published', true))
            ->sortBy('sort_order')
            ->values();
    }

    public static function forget(): void
    {
        static::$memo = null;
    }

    public static function assetUrl(?string $path, string $fallback = 'assets/images/media/img_87.jpg'): string
    {
        if (! $path) {
            return asset($fallback);
        }

        return str_starts_with($path, 'assets/')
            ? asset($path)
            : asset('storage/'.$path);
    }

    public static function href(?string $url, string $fallback = '#'): string
    {
        if (! filled($url)) {
            return $fallback;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:')) {
            return $url;
        }

        return url($url);
    }

    /**
     * @return array{sections: Collection<string, SiteSection>, slides: Collection, partners: Collection}
     */
    private static function payload(): array
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        if (! Schema::hasTable('site_sections')) {
            return static::$memo = [
                'sections' => collect(),
                'slides' => collect(),
                'partners' => collect(),
            ];
        }

        return static::$memo = [
            'sections' => SiteSection::query()->get()->keyBy('key'),
            'slides' => SiteSlide::query()->orderBy('sort_order')->get(),
            'partners' => Partner::query()->orderBy('sort_order')->get(),
        ];
    }
}
