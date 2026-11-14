<?php

namespace App\Models\Chrome;

use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSlide extends Model
{
    use ResolvesPublicMedia;

    public const PLACEMENT_HERO = 'hero';

    public const PLACEMENT_FEATURE = 'feature';

    public const PLACEMENT_CLUSTER = 'cluster';

    protected $fillable = [
        'placement',
        'title',
        'highlight',
        'body',
        'tags',
        'image',
        'link_url',
        'link_label',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
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

    public function scopePlacement(Builder $query, string $placement): Builder
    {
        return $query->where('placement', $placement)->orderBy('sort_order');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->image);
    }
}
