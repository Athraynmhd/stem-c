<?php

namespace App\Models\Concerns;

use App\Cms\Navigation\SiteMenu;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mengosongkan cache menu publik saat model yang memengaruhi submenu berubah.
 */
trait ClearsSiteMenuCache
{
    protected static function bootClearsSiteMenuCache(): void
    {
        static::saved(fn () => SiteMenu::forget());
        static::deleted(fn () => SiteMenu::forget());

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(fn () => SiteMenu::forget());
            static::forceDeleted(fn () => SiteMenu::forget());
        }
    }
}
