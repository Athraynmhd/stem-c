<?php

namespace App\Providers;

use App\Cms\Access\CmsAccess;
use App\Cms\Chrome\SiteContent;
use App\Cms\Media\EditableImage;
use App\Cms\Media\ImageOptimizer;
use App\Cms\Navigation\SiteMenu;
use App\Filament\Admin\Pages\Site\ManageHeaderFooter;
use App\Models\Auth\User;
use App\Models\Chrome\Setting;
use App\Models\Cms\LoginActivityLog;
use Filament\Forms\Components\FileUpload;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination.stemc');
        Paginator::defaultSimpleView('pagination.stemc');

        Carbon::setLocale(config('app.locale'));

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        $this->forceFilamentPublicDisk();
        $this->shareMenuData();
        $this->shareChromeData();
        $this->registerInlineImageDirective();
        $this->catatAktivitasLogin();
    }

    /**
     * Disk bawaan Filament mengikuti FILESYSTEM_DISK (`local` = storage/app/private)
     * yang tidak bisa diakses browser, sedangkan view menyajikan berkas lewat
     * `asset('storage/...')`. Dipaksa ke disk publik agar unggahan dari panel dan
     * dari editor inline mendarat serta terbaca di tempat yang sama.
     */
    private function forceFilamentPublicDisk(): void
    {
        FileUpload::configureUsing(function (FileUpload $upload): void {
            $upload->disk('public');
            $upload->visibility('public');
            // Hindari FilePond macet "Menunggu ukuran berkas" setelah Simpan
            // (temp Livewire sudah dipindah ImageOptimizer).
            $upload->fetchFileInformation(false);
            $upload->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                $storage = $component->getDisk();

                if (! $storage->exists($file)) {
                    return null;
                }

                $name = is_array($storedFileNames)
                    ? ($storedFileNames[$file] ?? basename($file))
                    : ($storedFileNames ?? basename($file));

                return [
                    'name' => $name,
                    'size' => (int) $storage->size($file),
                    'type' => rescue(fn () => $storage->mimeType($file), 'image/jpeg', report: false),
                    'url' => $storage->url($file),
                ];
            });
            $upload->saveUploadedFileUsing(function (FileUpload $component, UploadedFile $file): string {
                $directory = trim((string) ($component->getDirectory() ?? 'uploads'), '/');
                $folder = $directory !== '' ? $directory : 'uploads';
                $user = Auth::user() instanceof User ? Auth::user() : null;

                // Raster dikompresi; SVG dibersihkan (bukan ditolak) karena logo
                // institusi sering SVG dan diganti lewat dashboard.
                if (ImageOptimizer::shouldOptimize($file)) {
                    return ImageOptimizer::store($file, $folder, $user);
                }

                $stored = $file->store($folder, 'public');

                if (is_string($stored)) {
                    ImageOptimizer::catatBerkas($file, $stored, $user);
                }

                return is_string($stored) ? $stored : $folder.'/'.$file->hashName();
            });
        });
    }

    /**
     * `@editableImage('slide:3')` mencetak atribut penanda bila pengguna saat
     * ini berhak mengganti gambar tersebut, dan string kosong bila tidak.
     */
    private function registerInlineImageDirective(): void
    {
        Blade::directive('editableImage', fn (string $expression) => "<?php echo \App\Cms\Media\EditableImage::attribute({$expression}); ?>");
    }

    private function catatAktivitasLogin(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                LoginActivityLog::catat('login', $event->user);
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user instanceof User) {
                LoginActivityLog::catat('logout', $event->user);
            }
        });

        Event::listen(Failed::class, function (Failed $event): void {
            $user = $event->user instanceof User ? $event->user : null;
            $email = is_string($event->credentials['email'] ?? null) ? $event->credentials['email'] : null;
            LoginActivityLog::catat('failed', $user, $email);
        });
    }

    private function shareMenuData(): void
    {
        View::composer('components.site.header', function ($view) {
            $siap = Schema::hasTable('pages') && Schema::hasTable('documents');

            $view->with('menuUtama', $siap ? SiteMenu::build() : []);
        });
    }

    /**
     * Header dan footer memakai settings + section chrome yang sama supaya
     * sidenav tidak lagi hardcode alamat/email terpisah dari footer.
     */
    private function shareChromeData(): void
    {
        $composer = function ($view) {
            $setting = fn (string $key, ?string $default = null) => Schema::hasTable('settings')
                ? Setting::get($key, $default)
                : $default;

            $footerCta = Schema::hasTable('site_sections')
                ? SiteContent::section('footer.cta')
                : null;

            $footerNav = Schema::hasTable('site_sections')
                ? (SiteContent::section('footer.nav')?->meta('columns') ?? [])
                : [];

            if (empty($footerNav)) {
                $footerNav = ManageHeaderFooter::defaultNavColumns();
            }

            $chrome = [
                'siteName' => $setting('site_name', 'Pusat Riset STEM-C USK'),
                'tagline' => $setting('site_tagline', 'Science, Technology, Engineering, Mathematics - Character'),
                'email' => $setting('email', 'stemc@usk.ac.id'),
                'phone' => $setting('phone'),
                'address' => $setting('address', 'Lab Terpadu, Universitas Syiah Kuala, Banda Aceh, Aceh, Indonesia'),
                'logoLight' => $setting('logo_light', 'assets/images/logo/logo-stemc-light.svg'),
                'logoDark' => $setting('logo_dark', 'assets/images/logo/logo-stemc-dark.svg'),
                'favicon' => $setting('favicon', 'assets/images/fav-icon/icon.png'),
                'preloaderLogo' => $setting('preloader_logo', 'assets/images/loader-stemc.svg'),
                'watermark' => $setting('footer_watermark', 'STEM-C'),
                'metaDescription' => $setting('meta_description'),
                'headerCtaLabel' => $setting('header_cta_label', 'Ajukan Kerja Sama'),
                'headerCtaUrl' => $setting('header_cta_url', '/kerja-sama/ajukan'),
                'analyticsId' => $setting('analytics_id') ?: config('services.analytics.id'),
                'social' => array_filter([
                    'WhatsApp' => $setting('whatsapp'),
                    'Instagram' => $setting('instagram'),
                    'YouTube' => $setting('youtube'),
                    'Facebook' => $setting('facebook'),
                ]),
                'footerCta' => $footerCta,
                'canInlineEdit' => ($pengguna = Auth::user()) instanceof User && EditableImage::canEditAny($pengguna),
                'homepageAdminUrl' => ($pengguna = Auth::user()) instanceof User && CmsAccess::can($pengguna, CmsAccess::HOMEPAGE)
                    ? url('/admin/beranda')
                    : null,
            ];

            $view->with('chrome', $chrome);
            $view->with('footer', [
                'siteName' => $chrome['siteName'],
                'tagline' => $chrome['tagline'],
                'email' => $chrome['email'],
                'phone' => $chrome['phone'],
                'address' => $chrome['address'],
                'social' => $chrome['social'],
                'cta' => $footerCta,
                'nav' => $footerNav,
                'watermark' => $chrome['watermark'],
            ]);
        };

        View::composer([
            'components.site.header',
            'components.site.footer',
            'layouts.site',
        ], $composer);
    }
}
