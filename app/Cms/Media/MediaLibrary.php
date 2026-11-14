<?php

namespace App\Cms\Media;

use App\Cms\Html\SafeHtml;
use App\Models\Chrome\Partner;
use App\Models\Chrome\Setting;
use App\Models\Chrome\SiteSection;
use App\Models\Chrome\SiteSlide;
use App\Models\Content\Event;
use App\Models\Content\Innovation;
use App\Models\Content\MediaItem;
use App\Models\Content\News;
use App\Models\Content\Page;
use App\Models\Content\ResearchGroup;
use App\Models\Content\Researcher;
use Illuminate\Support\Facades\Schema;

/**
 * Utilitas pustaka media di luar panel: daftar path yang masih “hidup”
 * dipakai prune berkas pengganti inline.
 */
final class MediaLibrary
{
    /**
     * Kunci Setting merek yang menyimpan path gambar.
     *
     * @var list<string>
     */
    public const SETTING_KEYS = [
        'logo_light',
        'logo_dark',
        'favicon',
        'preloader_logo',
    ];

    /**
     * @return list<string>
     */
    public static function livePaths(): array
    {
        $paths = [];

        if (Schema::hasTable('settings')) {
            foreach (self::SETTING_KEYS as $key) {
                $value = Setting::get($key);
                if (is_string($value) && $value !== '') {
                    $paths[] = $value;
                }
            }
        }

        $paths = [
            ...$paths,
            ...self::kolom('site_slides', SiteSlide::class, 'image'),
            ...self::kolom('partners', Partner::class, 'logo'),
            ...self::kolom('site_sections', SiteSection::class, 'image'),
            ...self::kolom('news', News::class, 'cover_image'),
            ...self::kolom('events', Event::class, 'cover_image'),
            ...self::kolom('pages', Page::class, 'hero_image'),
            ...self::kolom('media_items', MediaItem::class, 'image'),
            ...self::kolom('researchers', Researcher::class, 'photo'),
            ...self::kolom('innovations', Innovation::class, 'image'),
            ...self::kolom('research_groups', ResearchGroup::class, 'image'),
            ...self::htmlPaths('events', Event::class, ['body', 'body_en']),
            ...self::htmlPaths('news', News::class, ['body', 'body_en']),
            ...self::htmlPaths('pages', Page::class, ['content', 'content_en']),
        ];

        $paths = array_values(array_unique(array_filter(
            $paths,
            static fn (mixed $path): bool => is_string($path) && $path !== '',
        )));

        return $paths;
    }

    /**
     * @param  class-string  $model
     * @return list<string>
     */
    private static function kolom(string $table, string $model, string $column): array
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return [];
        }

        return $model::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->pluck($column)
            ->all();
    }

    /**
     * @param  class-string  $model
     * @param  list<string>  $columns
     * @return list<string>
     */
    private static function htmlPaths(string $table, string $model, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $select = array_values(array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($select === []) {
            return [];
        }

        $paths = [];
        $query = $model::query()->select($select);
        if (method_exists($model, 'withTrashed')) {
            $query->withTrashed();
        }

        foreach ($query->cursor() as $row) {
            foreach ($select as $column) {
                $paths = [
                    ...$paths,
                    ...SafeHtml::storagePathsFromHtml($row->getAttributes()[$column] ?? null),
                ];
            }
        }

        return $paths;
    }
}
