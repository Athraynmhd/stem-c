<?php

namespace App\Models\Chrome;

use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Partner extends Model
{
    use ResolvesPublicMedia;

    protected $fillable = [
        'name',
        'logo',
        'url',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
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
        return $query->where('is_published', true)->orderBy('sort_order');
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->logo, 'assets/images/logo/partner/usk.png');
    }
}
