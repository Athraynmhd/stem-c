<?php

namespace App\Cms\Media;

use App\Cms\Access\CmsAccess;
use App\Cms\Chrome\SiteContent;
use App\Models\Auth\User;
use App\Models\Chrome\Partner;
use App\Models\Chrome\Setting;
use App\Models\Chrome\SiteSection;
use App\Models\Chrome\SiteSlide;
use App\Models\Cms\MediaEditLog;
use App\Models\Content\Event;
use App\Models\Content\Innovation;
use App\Models\Content\MediaItem;
use App\Models\Content\News;
use App\Models\Content\Page;
use App\Models\Content\Researcher;
use App\Models\Content\ResearchGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Daftar putih gambar yang boleh diganti langsung dari halaman publik.
 *
 * Penanda di Blade hanya menyebut target (misalnya "slide:3"); kelas ini yang
 * memutuskan kolom mana yang boleh ditulis, ke direktori mana berkasnya
 * disimpan, dan modul CmsAccess mana yang mengizinkannya. Tanpa daftar putih
 * ini, satu endpoint unggah bisa dipakai menulis kolom sembarang.
 *
 * Tiga lapis izin harus lolos semuanya: sakelar config/cms.php, daftar role
 * yang diperbolehkan, lalu matriks modul CmsAccess milik pengguna.
 */
final class EditableImage
{
    /** Kunci Setting yang berisi gambar merek. */
    private const SETTING_KEYS = [
        'logo_light',
        'logo_dark',
        'favicon',
        'preloader_logo',
    ];

    /**
     * type => [kelas model, kolom gambar, direktori simpan, modul CmsAccess]
     *
     * Direktori sengaja sama dengan FileUpload di panel supaya berkas hasil
     * unggahan inline dan unggahan dashboard tercampur rapi di satu tempat.
     *
     * @var array<string, array{0: class-string<Model>, 1: string, 2: string, 3: string}>
     */
    private const MODELS = [
        'slide' => [SiteSlide::class, 'image', 'site/slides', CmsAccess::HOMEPAGE],
        'partner' => [Partner::class, 'logo', 'site/partners', CmsAccess::HOMEPAGE],
        'news' => [News::class, 'cover_image', 'news', CmsAccess::NEWS],
        'event' => [Event::class, 'cover_image', 'events', CmsAccess::EVENTS],
        'page' => [Page::class, 'hero_image', 'pages', CmsAccess::PAGES],
        'media' => [MediaItem::class, 'image', 'gallery', CmsAccess::MEDIA],
        'researcher' => [Researcher::class, 'photo', 'researchers', CmsAccess::RESEARCHERS],
        'innovation' => [Innovation::class, 'image', 'innovations', CmsAccess::RESEARCH],
        'research_group' => [ResearchGroup::class, 'image', 'research-groups', CmsAccess::RESEARCH_GROUPS],
    ];

    /**
     * Sakelar utama. Bawaannya mati di produksi; lihat config/cms.php.
     */
    public static function inlineEnabled(): bool
    {
        return (bool) config('cms.inline_edit.enabled', false);
    }

    /**
     * Role yang boleh memakai editor inline. Sengaja lebih sempit daripada
     * daftar role yang boleh masuk panel: mengedit dari halaman publik tidak
     * punya layar konfirmasi seperti form dashboard.
     */
    public static function roleDiizinkan(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        /** @var array<int, string> $roles */
        $roles = (array) config('cms.inline_edit.roles', []);

        return $roles !== [] && $user->hasAnyRole($roles);
    }

    /**
     * Apakah pengguna ini boleh mengganti minimal satu jenis gambar? Dipakai
     * untuk memutuskan pemuatan skrip editor inline.
     */
    public static function canEditAny(?User $user): bool
    {
        if (! self::inlineEnabled() || ! self::roleDiizinkan($user)) {
            return false;
        }

        foreach ([
            CmsAccess::SITE_CHROME,
            CmsAccess::HOMEPAGE,
            CmsAccess::NEWS,
            CmsAccess::EVENTS,
            CmsAccess::PAGES,
            CmsAccess::MEDIA,
            CmsAccess::RESEARCHERS,
            CmsAccess::RESEARCH,
            CmsAccess::RESEARCH_GROUPS,
        ] as $module) {
            if (CmsAccess::can($user, $module)) {
                return true;
            }
        }

        return false;
    }

    public static function can(?User $user, string $target): bool
    {
        if (! self::inlineEnabled() || ! self::roleDiizinkan($user)) {
            return false;
        }

        $module = self::module($target);

        return $module !== null && CmsAccess::can($user, $module);
    }

    /**
     * Aturan validasi unggahan, diambil dari config supaya endpoint dan pesan
     * galat tidak pernah lebih longgar daripada kebijakan yang disetel.
     *
     * @return array<int, string>
     */
    public static function uploadRules(): array
    {
        /** @var array<int, string> $mimes */
        $mimes = (array) config('cms.inline_edit.mimes', ['jpg', 'jpeg', 'png', 'webp']);

        return [
            'required',
            'image',
            'mimes:'.implode(',', $mimes),
            'max:'.self::maxKilobytes(),
        ];
    }

    /**
     * Batas ukuran efektif dalam kilobyte.
     *
     * Nilai config dipangkas oleh batas PHP. Kalau config lebih besar daripada
     * upload_max_filesize, berkas di antara dua angka itu dibuang PHP sebelum
     * Laravel melihatnya — aturan `uploaded` gagal dan pengguna hanya menerima
     * "The image failed to upload." tanpa petunjuk apa pun.
     */
    public static function maxKilobytes(): int
    {
        $config = (int) config('cms.inline_edit.max_kb', 10240);
        $php = self::phpMaxKilobytes();

        return $php > 0 ? min($config, $php) : $config;
    }

    /**
     * Batas PHP yang paling ketat di antara upload_max_filesize dan
     * post_max_size. Nol berarti tidak ada batas yang bisa dibaca.
     */
    public static function phpMaxKilobytes(): int
    {
        $batas = array_filter([
            self::keKilobyte(ini_get('upload_max_filesize')),
            self::keKilobyte(ini_get('post_max_size')),
        ]);

        return $batas === [] ? 0 : (int) min($batas);
    }

    private static function keKilobyte(string|false $nilai): int
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '' || $nilai === '0' || $nilai === '-1') {
            return 0;
        }

        $angka = (float) $nilai;

        $pengali = match (strtolower(substr($nilai, -1))) {
            'g' => 1024 * 1024,
            'm' => 1024,
            'k' => 1,
            default => 1 / 1024,
        };

        return (int) floor($angka * $pengali);
    }

    /**
     * Nilai atribut accept untuk dialog pilih berkas, diturunkan dari daftar
     * mime yang sama agar peramban tidak menawarkan format yang akan ditolak.
     */
    public static function acceptAttribute(): string
    {
        /** @var array<int, string> $mimes */
        $mimes = (array) config('cms.inline_edit.mimes', ['jpg', 'jpeg', 'png', 'webp']);

        $tipe = array_map(
            fn (string $mime): string => 'image/'.($mime === 'jpg' ? 'jpeg' : $mime),
            $mimes,
        );

        return implode(',', array_unique($tipe));
    }

    /**
     * Atribut penanda untuk Blade. Mengembalikan string kosong bila pengguna
     * saat ini tidak berhak, sehingga pengunjung biasa tidak melihat jejak
     * apa pun di HTML.
     */
    public static function attribute(string $target): string
    {
        $user = Auth::user();

        if (! $user instanceof User || ! self::can($user, $target)) {
            return '';
        }

        return 'data-stemc-image-edit="'.e($target).'"';
    }

    /**
     * Ganti gambar target lalu kembalikan URL publik yang baru. Setiap
     * penggantian dicatat di log audit sebelum respons dikirim.
     */
    public static function replace(string $target, UploadedFile $file, User $user, ?Request $request = null): string
    {
        [$type, $id] = self::parse($target);

        $baru = self::simpan($file, self::directory($type), $user);
        $lama = self::tulis($type, $id, $baru);

        MediaEditLog::catat($user, $target, $lama, $baru, MediaEditLog::ACTION_REPLACE, $request);

        return asset('storage/'.$baru);
    }

    /**
     * Kembalikan gambar ke versi sebelum penggantian yang tercatat di log.
     * Dipanggil dari panel admin, jadi tidak melewati gerbang inline.
     */
    public static function restore(MediaEditLog $log, User $user, ?Request $request = null): void
    {
        if (! $log->dapatDipulihkan()) {
            throw new RuntimeException('Catatan ini tidak menyimpan versi sebelumnya.');
        }

        $path = (string) $log->old_path;

        if (! self::berkasTersedia($path)) {
            throw new RuntimeException('Berkas versi sebelumnya sudah tidak ada di penyimpanan.');
        }

        [$type, $id] = self::parse($log->target);

        $lama = self::tulis($type, $id, $path);

        MediaEditLog::catat($user, $log->target, $lama, $path, MediaEditLog::ACTION_RESTORE, $request);
    }

    public static function berkasTersedia(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        // Gambar bawaan template hidup di public/assets, bukan di disk storage.
        if (str_starts_with($path, 'assets/')) {
            return file_exists(public_path($path));
        }

        return Storage::disk('public')->exists($path);
    }

    /**
     * Tulis path gambar ke target lalu kembalikan path yang tergantikan.
     */
    private static function tulis(string $type, string $id, string $path): ?string
    {
        if ($type === 'setting') {
            $lama = Setting::get($id);
            Setting::set($id, $path, 'brand');

            return is_string($lama) ? $lama : null;
        }

        if ($type === 'section') {
            $section = SiteSection::query()->where('key', $id)->firstOrFail();
            $lama = $section->image;
            $section->image = $path;
            $section->save();

            SiteContent::forget();

            return $lama;
        }

        [$class, $field] = self::MODELS[$type];

        /** @var Model $record */
        $record = $class::query()->findOrFail($id);

        $lama = $record->{$field};
        $record->{$field} = $path;
        $record->save();

        SiteContent::forget();

        return $lama;
    }

    private static function directory(string $type): string
    {
        return match ($type) {
            'setting' => 'brand',
            'section' => 'site/banners',
            default => self::MODELS[$type][2],
        };
    }

    private static function simpan(UploadedFile $file, string $directory, User $user): string
    {
        return ImageOptimizer::store($file, $directory, $user);
    }

    private static function module(string $target): ?string
    {
        try {
            [$type, $id] = self::parse($target);
        } catch (\InvalidArgumentException) {
            return null;
        }

        return match ($type) {
            'setting' => CmsAccess::SITE_CHROME,
            // Section beranda dikelola halaman "Beranda"; banner halaman lain
            // ikut modul chrome situs.
            'section' => str_starts_with($id, 'home.') ? CmsAccess::HOMEPAGE : CmsAccess::SITE_CHROME,
            default => self::MODELS[$type][3],
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function parse(string $target): array
    {
        $bagian = explode(':', $target, 2);

        if (count($bagian) !== 2) {
            throw new \InvalidArgumentException('Target gambar tidak sah.');
        }

        [$type, $id] = $bagian;

        $sah = match ($type) {
            'setting' => in_array($id, self::SETTING_KEYS, true),
            'section' => (bool) preg_match('/^[a-z_]+\.[a-z_]+$/', $id),
            default => isset(self::MODELS[$type]) && ctype_digit($id),
        };

        if (! $sah) {
            throw new \InvalidArgumentException('Target gambar tidak sah.');
        }

        return [$type, $id];
    }
}
