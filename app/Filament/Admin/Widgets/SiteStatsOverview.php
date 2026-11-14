<?php

namespace App\Filament\Admin\Widgets;

use App\Cms\Access\CmsAccess;
use App\Filament\Admin\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Admin\Resources\Events\EventResource;
use App\Filament\Admin\Resources\Innovations\InnovationResource;
use App\Filament\Admin\Resources\News\NewsResource;
use App\Filament\Admin\Resources\Publications\PublicationResource;
use App\Filament\Admin\Resources\Researchers\ResearcherResource;
use App\Models\Contact\ContactMessage;
use App\Models\Content\Event;
use App\Models\Content\Innovation;
use App\Models\Content\News;
use App\Models\Content\Publication;
use App\Models\Content\Researcher;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SiteStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::check();
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        $stats = [];

        if (CmsAccess::can($user, CmsAccess::RESEARCHERS)) {
            $stats[] = Stat::make('Peneliti tayang', Researcher::query()->published()->count())
                ->description('Profil publik')
                ->url(ResearcherResource::getUrl())
                ->icon('heroicon-o-user-group');
        }

        if (CmsAccess::can($user, CmsAccess::PUBLICATIONS)) {
            $stats[] = Stat::make('Publikasi tayang', Publication::query()->published()->count())
                ->description('Termasuk HKI di katalog publikasi')
                ->url(PublicationResource::getUrl())
                ->icon('heroicon-o-book-open');
        }

        if (CmsAccess::can($user, CmsAccess::RESEARCH)) {
            $stats[] = Stat::make('HKI / produk', Innovation::query()->published()->count())
                ->description('Produk, paten, hak cipta, merek')
                ->url(InnovationResource::getUrl())
                ->icon('heroicon-o-light-bulb');
        }

        if (CmsAccess::can($user, CmsAccess::NEWS)) {
            $stats[] = Stat::make('Berita tayang', News::query()->published()->count())
                ->description('Artikel published')
                ->url(NewsResource::getUrl())
                ->icon('heroicon-o-newspaper');
        }

        if (CmsAccess::can($user, CmsAccess::EVENTS)) {
            $perluArsip = Event::query()->dueForArchive()->count();
            $segera = Event::query()->endingSoon(7)->count();

            $stats[] = Stat::make('Agenda mendatang', Event::query()->published()->upcoming()->count())
                ->description('Kegiatan belum berlangsung')
                ->url(EventResource::getUrl())
                ->icon('heroicon-o-calendar-days');

            $stats[] = Stat::make('Perlu diarsipkan', (string) $perluArsip)
                ->description($segera > 0
                    ? $segera.' agenda berakhir dalam 7 hari. Sistem → Pemeliharaan untuk arsip.'
                    : 'Agenda terbit yang tanggal selesainya sudah lewat')
                ->url(url('/admin/pemeliharaan'))
                ->icon('heroicon-o-archive-box')
                ->color($perluArsip > 0 ? 'warning' : 'gray');
        }

        if (CmsAccess::can($user, CmsAccess::CONTACT)) {
            $stats[] = Stat::make('Pesan baru', ContactMessage::query()->where('status', 'new')->count())
                ->description('Kotak masuk kontak')
                ->url(ContactMessageResource::getUrl())
                ->icon('heroicon-o-envelope');
        }

        if (CmsAccess::can($user, CmsAccess::HOMEPAGE)) {
            $stats[] = Stat::make('Edit Beranda', 'Buka')
                ->description('Dari situs publik: buka/tutup bilah edit')
                ->url(url('/?edit=1'))
                ->openUrlInNewTab(false)
                ->icon('heroicon-o-home-modern');
        }

        return $stats;
    }
}
