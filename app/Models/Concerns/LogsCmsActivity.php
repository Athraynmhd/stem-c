<?php

namespace App\Models\Concerns;

use App\Models\Cms\ContentActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Mencatat create/update/delete/restore ke content_activity_logs.
 * Dipasang lewat bootLogsCmsActivity pada model konten yang relevan.
 */
trait LogsCmsActivity
{
    public static function bootLogsCmsActivity(): void
    {
        static::created(function (Model $model): void {
            ContentActivityLog::catat('created', $model);
        });

        static::updated(function (Model $model): void {
            $dirty = $model->getChanges();
            unset($dirty['updated_at']);

            if ($dirty === []) {
                return;
            }

            ContentActivityLog::catat('updated', $model, $dirty);
        });

        static::deleted(function (Model $model): void {
            ContentActivityLog::catat('deleted', $model);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model): void {
                ContentActivityLog::catat('restored', $model);
            });
        }
    }
}
