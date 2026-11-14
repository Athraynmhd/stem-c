<?php

namespace App\Models\Content;

use App\Cms\Html\SafeHtml;
use App\Models\Concerns\ClearsSiteMenuCache;
use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsCmsActivity;
use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use ClearsSiteMenuCache;
    use HasOptionalLocale;
    use HasSeo;
    use LogsCmsActivity;
    use ResolvesPublicMedia;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'title_en',
        'slug',
        'section',
        'section_en',
        'content',
        'content_en',
        'hero_image',
        'seo_title',
        'seo_description',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getHeroUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->hero_image);
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => SafeHtml::clean($value),
            set: fn (?string $value) => SafeHtml::clean($value),
        );
    }

    protected function contentEn(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => SafeHtml::clean($value),
            set: fn (?string $value) => SafeHtml::clean($value),
        );
    }
}
