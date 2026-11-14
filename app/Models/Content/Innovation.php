<?php

namespace App\Models\Content;

use App\Cms\Access\CmsAccess;
use App\Models\Auth\User;
use App\Models\Concerns\ClearsSiteMenuCache;
use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasWorkflow;
use App\Models\Concerns\LogsCmsActivity;
use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Innovation extends Model
{
    use ClearsSiteMenuCache;
    use HasOptionalLocale;
    use HasSeo;
    use HasWorkflow;
    use LogsCmsActivity;
    use ResolvesPublicMedia;
    use SoftDeletes;

    public const TYPES = [
        'produk' => 'Produk & Inovasi',
        'paten' => 'Paten',
        'hak_cipta' => 'Hak Cipta',
        'merek' => 'Merek',
    ];

    protected $fillable = [
        'created_by',
        'title',
        'title_en',
        'slug',
        'type',
        'description',
        'description_en',
        'specifications',
        'image',
        'registration_number',
        'year',
        'holders',
        'hki_status',
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

    public function scopeProducts(Builder $query): Builder
    {
        return $query->where('type', 'produk');
    }

    public function scopeHki(Builder $query): Builder
    {
        return $query->whereIn('type', ['paten', 'hak_cipta', 'merek']);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getImageUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->image, 'assets/images/media/img_01.jpg');
    }
}
