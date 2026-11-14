<?php

namespace App\Models\Content;

use App\Cms\Html\SafeHtml;
use App\Models\Auth\User;
use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsCmsActivity;
use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasOptionalLocale;
    use HasSeo;
    use LogsCmsActivity;
    use ResolvesPublicMedia;
    use SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'title_en',
        'slug',
        'category',
        'excerpt',
        'excerpt_en',
        'body',
        'body_en',
        'cover_image',
        'tags',
        'status',
        'published_at',
        'author_id',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'tags' => 'array',
        ];
    }

    /**
     * @return list<string>
     */
    public function tagList(): array
    {
        $tags = $this->tags;

        if (! is_array($tags)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($tag): string => trim((string) $tag),
            $tags,
        )));
    }

    public function scopeTagged(Builder $query, string $tag): Builder
    {
        $tag = trim($tag);

        return $tag === '' ? $query : $query->whereJsonContains('tags', $tag);
    }

    public static function tagTayang(): array
    {
        return static::query()
            ->published()
            ->get(['tags'])
            ->flatMap(fn (self $item) => $item->tagList())
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getCoverUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->cover_image);
    }

    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => SafeHtml::clean($value),
            set: fn (?string $value) => SafeHtml::clean($value),
        );
    }

    protected function bodyEn(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => SafeHtml::clean($value),
            set: fn (?string $value) => SafeHtml::clean($value),
        );
    }
}
