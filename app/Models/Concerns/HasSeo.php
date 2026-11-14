<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSeo
{
    public function initializeHasSeo(): void
    {
        foreach (['seo_title', 'seo_description'] as $field) {
            if (! in_array($field, $this->fillable, true)) {
                $this->fillable[] = $field;
            }
        }
    }

    public function seoTitle(): string
    {
        return (string) ($this->getAttribute('seo_title')
            ?: $this->localized('title')
            ?? $this->getAttribute('title')
            ?? $this->getAttribute('name')
            ?? '');
    }

    public function seoDescription(): ?string
    {
        $value = $this->getAttribute('seo_description')
            ?: $this->getAttribute('excerpt')
            ?: $this->getAttribute('abstract')
            ?: $this->getAttribute('expertise');

        return $value ? Str::limit(strip_tags((string) $value), 160) : null;
    }
}
