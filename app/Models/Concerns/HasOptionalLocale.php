<?php

namespace App\Models\Concerns;

/**
 * Nilai kolom yang mengikuti locale aktif (id/en).
 * Kolom EN opsional: kosong/whitespace jatuh ke Indonesia.
 */
trait HasOptionalLocale
{
    public function localized(string $field, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();
        $id = $this->getAttribute($field);

        if ($locale !== 'en') {
            return $id;
        }

        $en = $this->getAttribute($field.'_en');

        return is_string($en) && trim($en) !== '' ? $en : $id;
    }
}
