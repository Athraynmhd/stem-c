<?php

namespace App\Models\Content;

use App\Cms\Html\SafeHtml;
use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsCmsActivity;
use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasOptionalLocale;
    use HasSeo;
    use LogsCmsActivity;
    use ResolvesPublicMedia;
    use SoftDeletes;

    public const CATEGORIES = [
        'seminar' => 'Seminar Nasional',
        'workshop' => 'Workshop',
        'pelatihan' => 'Pelatihan',
        'fgd' => 'Focus Group Discussion',
        'stem-camp' => 'STEM Camp',
        'kunjungan' => 'Kunjungan',
        'kompetisi' => 'Kompetisi',
        'call-for-paper' => 'Call for Paper',
    ];

    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'cover_image',
        'location',
        'starts_at',
        'ends_at',
        'registration_url',
        'status',
        'title_en',
        'excerpt_en',
        'body_en',
        'seo_title',
        'seo_description',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_archived' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('starts_at', '<', now());
    }

    public function scopeDueForArchive(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where('is_archived', false)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now());
    }

    public function scopeEndingSoon(Builder $query, int $days = 7): Builder
    {
        return $query
            ->where('status', 'published')
            ->where('is_archived', false)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->starts_at !== null && $this->starts_at->isFuture();
    }

    public function getCategoryLabelAttribute(): ?string
    {
        return $this->category ? (self::CATEGORIES[$this->category] ?? $this->category) : null;
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

    public function getCoverUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->cover_image);
    }
}
