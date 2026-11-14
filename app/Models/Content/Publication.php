<?php

namespace App\Models\Content;

use App\Cms\Access\CmsAccess;
use App\Models\Auth\User;
use App\Models\Concerns\ClearsSiteMenuCache;
use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasWorkflow;
use App\Models\Concerns\LogsCmsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Publication extends Model
{
    use ClearsSiteMenuCache;
    use HasOptionalLocale;
    use HasSeo;
    use HasWorkflow;
    use LogsCmsActivity;

    public const TYPES = [
        'jurnal' => 'Artikel Jurnal',
        'prosiding' => 'Prosiding',
        'buku' => 'Buku & Bab Buku',
        'hki' => 'HKI',
        'paten' => 'Paten',
    ];

    protected $fillable = [
        'created_by',
        'title',
        'title_en',
        'authors',
        'type',
        'venue',
        'year',
        'doi',
        'url',
        'abstract',
        'abstract_en',
        'file',
        'seo_title',
        'seo_description',
        'is_featured',
        'is_published',
        'workflow_status',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('workflow_status', CmsAccess::WORKFLOW_PUBLISHED);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function researchers(): BelongsToMany
    {
        return $this->belongsToMany(Researcher::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getLinkAttribute(): ?string
    {
        if ($this->doi) {
            return str_starts_with($this->doi, 'http')
                ? $this->doi
                : 'https://doi.org/'.ltrim($this->doi, '/');
        }

        if ($this->url) {
            return $this->url;
        }

        return $this->file ? asset('storage/'.$this->file) : null;
    }
}
