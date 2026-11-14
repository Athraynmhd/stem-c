<?php

namespace App\Cms\Ops;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use ZipArchive;

/**
 * Cadangan data CMS + unggahan publik. Bukan dump .env.
 * Restore menimpa isi tabel aplikasi; hanya untuk super admin.
 */
final class SiteBackup
{
    private const SKIP = [
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        'migrations',
        'password_reset_tokens',
    ];

    public static function buat(): string
    {
        $nama = 'stemc-'.now()->format('Ymd-His').'.zip';
        $dir = storage_path('app/private/'.self::folder());
        File::ensureDirectoryExists($dir);
        $zipPath = $dir.DIRECTORY_SEPARATOR.$nama;

        $tmp = $dir.DIRECTORY_SEPARATOR.'_tmp-'.uniqid();
        File::ensureDirectoryExists($tmp);

        File::put($tmp.'/manifest.json', json_encode([
            'dibuat' => now()->toIso8601String(),
            'app' => config('app.name'),
            'driver' => config('database.default'),
            'catatan' => 'Tidak berisi .env. Pulihkan hanya di lingkungan yang sama skemanya.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        File::put($tmp.'/data.json', json_encode(self::eksporTabel(), JSON_UNESCAPED_UNICODE));

        $publicRoot = storage_path('app/public');
        if (is_dir($publicRoot)) {
            File::copyDirectory($publicRoot, $tmp.'/storage-public');
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            File::deleteDirectory($tmp);
            throw new \RuntimeException('Gagal membuat berkas cadangan.');
        }

        self::tambahFolderKeZip($zip, $tmp, '');
        $zip->close();
        File::deleteDirectory($tmp);

        self::pangkas();

        return $nama;
    }

    /**
     * @return list<array{name: string, size: string, modified: string}>
     */
    public static function daftar(): array
    {
        $disk = Storage::disk('local');
        $folder = self::folder();

        if (! $disk->exists($folder)) {
            return [];
        }

        $items = [];

        foreach ($disk->files($folder) as $path) {
            if (! str_ends_with($path, '.zip')) {
                continue;
            }

            $items[] = [
                'name' => basename($path),
                'size' => Number::fileSize((int) $disk->size($path)),
                'modified' => date('d M Y H:i', $disk->lastModified($path)),
            ];
        }

        usort($items, fn (array $a, array $b) => strcmp($b['name'], $a['name']));

        return $items;
    }

    public static function pathAbsolut(string $nama): string
    {
        $nama = basename($nama);
        abort_unless(preg_match('/^stemc-\d{8}-\d{6}\.zip$/', $nama) === 1, 404);

        $path = storage_path('app/private/'.self::folder().'/'.$nama);
        abort_unless(is_file($path), 404);

        return $path;
    }

    public static function pulihkan(string $nama): string
    {
        $zipPath = self::pathAbsolut($nama);
        $tmp = storage_path('app/private/'.self::folder().'/_restore-'.uniqid());
        File::ensureDirectoryExists($tmp);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Berkas cadangan tidak bisa dibuka.');
        }
        $zip->extractTo($tmp);
        $zip->close();

        $dataFile = $tmp.'/data.json';
        if (! is_file($dataFile)) {
            File::deleteDirectory($tmp);
            throw new \RuntimeException('Cadangan tidak berisi data.json.');
        }

        /** @var array<string, list<array<string, mixed>>> $data */
        $data = json_decode((string) File::get($dataFile), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(fn () => self::imporTabel($data));

        $unggahan = $tmp.'/storage-public';
        if (is_dir($unggahan)) {
            $tujuan = storage_path('app/public');
            File::ensureDirectoryExists($tujuan);
            File::copyDirectory($unggahan, $tujuan);
        }

        File::deleteDirectory($tmp);

        return 'Pulih dari '.$nama.'. Periksa situs publik dan panel admin.';
    }

    public static function hapus(string $nama): void
    {
        $path = self::pathAbsolut($nama);
        File::delete($path);
    }

    private static function folder(): string
    {
        return trim((string) config('cms.backup.directory', 'backups'), '/');
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private static function eksporTabel(): array
    {
        $out = [];

        foreach (Schema::getTableListing() as $tabel) {
            if (in_array($tabel, self::SKIP, true)) {
                continue;
            }

            $out[$tabel] = DB::table($tabel)->get()->map(fn ($row) => (array) $row)->all();
        }

        return $out;
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $data
     */
    private static function imporTabel(array $data): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            foreach ($data as $tabel => $baris) {
                if (! Schema::hasTable($tabel) || in_array($tabel, self::SKIP, true)) {
                    continue;
                }

                DB::table($tabel)->delete();

                foreach (array_chunk($baris, 100) as $chunk) {
                    if ($chunk !== []) {
                        DB::table($tabel)->insert($chunk);
                    }
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private static function tambahFolderKeZip(ZipArchive $zip, string $folder, string $prefix): void
    {
        $folder = rtrim($folder, '/\\');

        foreach (File::allFiles($folder) as $file) {
            $rel = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($folder))), '/');
            $zip->addFile($file->getPathname(), $prefix === '' ? $rel : $prefix.'/'.$rel);
        }
    }

    private static function pangkas(): void
    {
        $keep = max(3, (int) config('cms.backup.keep', 12));
        $semua = self::daftar();

        foreach (array_slice($semua, $keep) as $item) {
            self::hapus($item['name']);
        }
    }
}
