<?php

namespace App\Models\Content;

use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsCmsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partnership extends Model
{
    use HasOptionalLocale;
    use HasSeo;
    use LogsCmsActivity;
    use SoftDeletes;

    public const TYPES = [
        'mou' => 'MoU',
        'moa' => 'MoA',
    ];

    protected $fillable = [
        'title',
        'title_en',
        'partner_name',
        'type',
        'scope',
        'scope_en',
        'seo_title',
        'seo_description',
        'started_on',
        'ended_on',
        'is_active',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
