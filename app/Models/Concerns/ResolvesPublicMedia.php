<?php

namespace App\Models\Concerns;

trait ResolvesPublicMedia
{
    /**
     * Path seeder memakai folder aset publik; unggahan admin ada di storage.
     */
    protected function publicMediaUrl(?string $path, string $fallback = 'assets/images/media/img_87.jpg'): string
    {
        if (! $path) {
            return asset($fallback);
        }

        return str_starts_with($path, 'assets/')
            ? asset($path)
            : asset('storage/'.$path);
    }
}
