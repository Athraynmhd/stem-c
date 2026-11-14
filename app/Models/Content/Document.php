<?php

namespace App\Models\Content;

use App\Models\Concerns\ClearsSiteMenuCache;
use App\Models\Concerns\HasOptionalLocale;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsCmsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use ClearsSiteMenuCache;
    use HasOptionalLocale;
    use HasSeo;
    use LogsCmsActivity;
    use SoftDeletes;

    public const CATEGORIES = [
        'regulasi' => 'Regulasi & SK',
        'panduan' => 'Panduan',
        'formulir' => 'Formulir',
        'laporan' => 'Laporan',
        'materi' => 'Materi Kegiatan',
    ];

    protected $fillable = [
        'title',
        'title_en',
        'category',
        'description',
        'description_en',
        'seo_title',
        'seo_description',
        'file',
        'external_url',
        'file_size',
        'published_on',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'file_size' => 'integer',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->file) {
            return str_starts_with($this->file, 'assets/')
                ? asset($this->file)
                : asset('storage/'.$this->file);
        }

        return $this->external_url;
    }

    /**
     * Berkas yang ditautkan ke repositori luar dibuka di tab baru, sedangkan
     * berkas milik sendiri langsung diunduh lewat atribut download.
     */
    public function getIsExternalAttribute(): bool
    {
        return ! $this->file && (bool) $this->external_url;
    }

    public function getExtensionAttribute(): ?string
    {
        if (! $this->file) {
            return $this->external_url ? 'tautan' : null;
        }

        return strtoupper(pathinfo($this->file, PATHINFO_EXTENSION)) ?: null;
    }

    /**
     * Ukuran disimpan saat unggah agar daftar dokumen tidak perlu menyentuh
     * disk sekali per baris. Nilai lama yang belum terisi dibaca sekali sebagai
     * cadangan supaya data hasil impor tetap tampil wajar.
     */
    public function getSizeLabelAttribute(): ?string
    {
        $bytes = $this->file_size;

        if (! $bytes && $this->file && ! str_starts_with($this->file, 'assets/')) {
            $bytes = Storage::disk('public')->exists($this->file)
                ? Storage::disk('public')->size($this->file)
                : null;
        }

        if (! $bytes) {
            return null;
        }

        foreach ([['GB', 1073741824], ['MB', 1048576], ['KB', 1024]] as [$unit, $step]) {
            if ($bytes >= $step) {
                return round($bytes / $step, $bytes / $step >= 10 ? 0 : 1).' '.$unit;
            }
        }

        return $bytes.' B';
    }
}
