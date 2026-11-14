<?php

namespace App\Models\Chrome;

use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSection extends Model
{
    use ResolvesPublicMedia;

    protected $fillable = [
        'key',
        'eyebrow',
        'heading',
        'heading_highlight',
        'body',
        'cta_label',
        'cta_url',
        'image',
        'meta',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_content'));
        static::deleted(fn () => Cache::forget('site_content'));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return $this->publicMediaUrl($this->image);
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        return data_get($this->meta, $key, $default);
    }
}
