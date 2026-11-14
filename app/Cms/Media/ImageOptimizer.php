<?php

namespace App\Cms\Media;

use App\Models\Auth\User;
use App\Models\Cms\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Kompresi/resize unggahan gambar ke disk publik, lalu catat di media library.
 */
final class ImageOptimizer
{
    public static function store(UploadedFile $file, string $directory, ?User $user = null): string
    {
        $directory = trim($directory, '/');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = Str::uuid()->toString().'.'.$ext;
        $path = $directory.'/'.$name;

        if (SvgSanitizer::isSvg($file)) {
            $clean = SvgSanitizer::cleanFile($file);
            Storage::disk('public')->put($path, $clean);
            self::catat($file, $path, $user, strlen($clean));

            return $path;
        }

        if (self::isImage($file) && function_exists('imagecreatetruecolor')) {
            $binary = self::compress($file);
            if ($binary !== null) {
                Storage::disk('public')->put($path, $binary);
                self::catat($file, $path, $user, strlen($binary));

                return $path;
            }
        }

        $stored = $file->storeAs($directory, $name, 'public');
        self::catat($file, $stored, $user);

        return $stored;
    }

    public static function isImage(UploadedFile $file): bool
    {
        return str_starts_with((string) $file->getMimeType(), 'image/')
            && ! in_array($file->getMimeType(), ['image/svg+xml', 'image/svg'], true);
    }

    public static function shouldOptimize(UploadedFile $file): bool
    {
        return self::isImage($file) || SvgSanitizer::isSvg($file);
    }

    private static function compress(UploadedFile $file): ?string
    {
        $mime = $file->getMimeType();
        $source = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
            default => false,
        };

        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $max = 1920;

        if ($width > $max || $height > $max) {
            $scale = min($max / $width, $max / $height);
            $target = imagescale($source, (int) round($width * $scale), (int) round($height * $scale));
            imagedestroy($source);
            $source = $target ?: $source;
        }

        ob_start();
        match ($mime) {
            'image/png' => imagepng($source, null, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($source, null, 82) : imagejpeg($source, null, 82),
            default => imagejpeg($source, null, 82),
        };
        imagedestroy($source);

        return (string) ob_get_clean();
    }

    private static function catat(UploadedFile $file, string $path, ?User $user, ?int $size = null): void
    {
        if (! Schema::hasTable('media_assets')) {
            return;
        }

        $full = Storage::disk('public')->path($path);
        $info = @getimagesize($full) ?: [null, null];

        MediaAsset::query()->create([
            'user_id' => $user?->getKey(),
            'folder' => dirname($path),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $size ?? (int) $file->getSize(),
            'checksum' => @hash_file('sha256', $full) ?: null,
            'width' => $info[0],
            'height' => $info[1],
        ]);
    }

    public static function catatBerkas(UploadedFile $file, string $path, ?User $user, ?int $size = null): void
    {
        self::catat($file, $path, $user, $size);
    }
}
