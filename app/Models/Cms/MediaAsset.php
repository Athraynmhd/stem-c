<?php

namespace App\Models\Cms;

use App\Models\Auth\User;
use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaAsset extends Model
{
    use ResolvesPublicMedia;

    protected $fillable = [
        'user_id',
        'folder',
        'original_name',
        'path',
        'mime',
        'size',
        'checksum',
        'width',
        'height',
        'alt',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->path);
    }
}
