<?php

namespace App\Models\Content;

use App\Models\Auth\User;
use App\Models\Concerns\ClearsSiteMenuCache;
use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsCmsActivity;
use App\Models\Concerns\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Researcher extends Model
{
    use ClearsSiteMenuCache;
    use HasOptionalLocale;
    use HasSeo;
    use LogsCmsActivity;
    use ResolvesPublicMedia;
    use SoftDeletes;

    public const GROUPS = [
        'rnd' => 'RnD',
        'manufacturing' => 'Manufacturing',
        'pedagogical' => 'Pedagogical',
        'marketing' => 'Marketing & Collaboration',
    ];

    protected $fillable = [
        'user_id',
        'research_group_id',
        'name',
        'name_en',
        'slug',
        'degree',
        'photo',
        'expertise',
        'expertise_en',
        'affiliation',
        'research_group',
        'education',
        'interests',
        'bio',
        'bio_en',
        'email',
        'google_scholar',
        'scopus',
        'sinta',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class, 'research_group_id');
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(ResearchProject::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        return $this->publicMediaUrl($this->photo, 'assets/images/media/img_01.jpg');
    }

    public function getGroupLabelAttribute(): string
    {
        if ($this->group) {
            return (string) $this->group->localized('name');
        }

        return self::GROUPS[$this->research_group] ?? (string) $this->research_group;
    }

    /**
     * Nama yang ditampilkan di situs. Gelar (depan/belakang) ditulis langsung
     * di field name — tidak digabung dari kolom degree (kolom itu ditinggalkan).
     */
    public function getDisplayNameAttribute(): string
    {
        return (string) ($this->localized('name') ?: $this->name);
    }
}
