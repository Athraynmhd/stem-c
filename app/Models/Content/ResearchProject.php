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
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchProject extends Model
{
    use ClearsSiteMenuCache;
    use HasOptionalLocale;
    use HasSeo;
    use HasWorkflow;
    use LogsCmsActivity;
    use SoftDeletes;

    public const STATUSES = [
        'berjalan' => 'Berjalan',
        'selesai' => 'Selesai',
    ];

    protected $fillable = [
        'created_by',
        'research_group_id',
        'title',
        'title_en',
        'slug',
        'year',
        'leader',
        'members',
        'funding',
        'status',
        'abstract',
        'abstract_en',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class, 'research_group_id');
    }

    public function researchers(): BelongsToMany
    {
        return $this->belongsToMany(Researcher::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
