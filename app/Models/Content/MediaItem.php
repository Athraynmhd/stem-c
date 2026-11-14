<?php

namespace App\Models\Content;

use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    use ResolvesPublicMedia;

    protected $fillable = [
        'title',
        'type',
        'album',
        'image',
        'video_url',
        'captured_at',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'date',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getThumbnailAttribute(): string
    {
        return $this->publicMediaUrl($this->image);
    }

    /**
     * Fancybox membedakan foto dan video lewat target tautan, jadi video
     * memakai URL embed sementara foto memakai berkas gambarnya sendiri.
     */
    public function getLightboxUrlAttribute(): ?string
    {
        return $this->type === 'video'
            ? $this->video_url
            : $this->thumbnail;
    }
}
