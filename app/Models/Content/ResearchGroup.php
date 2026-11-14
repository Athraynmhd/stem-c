<?php

namespace App\Models\Content;

use App\Models\Concerns\ClearsSiteMenuCache;
use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsCmsActivity;
use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchGroup extends Model
{
    use ClearsSiteMenuCache;
    use HasOptionalLocale;
    use HasSeo;
    use LogsCmsActivity;
    use ResolvesPublicMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'code',
        'focus',
        'focus_en',
        'image',
        'seo_title',
        'seo_description',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function researchers(): HasMany
    {
        return $this->hasMany(Researcher::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(ResearchProject::class);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->image, 'assets/images/media/img_01.jpg');
    }
}
