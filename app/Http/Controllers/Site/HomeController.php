<?php

namespace App\Http\Controllers\Site;

use App\Cms\Chrome\SiteContent;
use App\Cms\Content\PublicStats;
use App\Http\Controllers\Controller;
use App\Models\Chrome\Setting;
use App\Models\Chrome\SiteSlide;
use App\Models\Content\Event;
use App\Models\Content\Innovation;
use App\Models\Content\MediaItem;
use App\Models\Content\News;
use App\Models\Content\Publication;
use App\Models\Content\ResearchProject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('site.home.index', [
            'siteName' => Setting::get('site_name', 'Pusat Riset STEM-C USK'),
            'tagline' => Setting::get('site_tagline', 'Science, Technology, Engineering, Mathematics - Character'),
            'heroSlides' => SiteContent::slides(SiteSlide::PLACEMENT_HERO),
            'featureSlides' => SiteContent::slides(SiteSlide::PLACEMENT_FEATURE),
            'clusterSlides' => SiteContent::slides(SiteSlide::PLACEMENT_CLUSTER),
            'partners' => SiteContent::partners(),
            'sectionAbout' => SiteContent::section('home.about'),
            'sectionFeature' => SiteContent::section('home.feature'),
            'sectionClusters' => SiteContent::section('home.clusters'),
            'sectionEvents' => SiteContent::section('home.events'),
            'sectionImagy' => SiteContent::section('home.imagy'),
            'sectionPublications' => SiteContent::section('home.publications'),
            'sectionNews' => SiteContent::section('home.news'),
            'sectionGallery' => SiteContent::section('home.gallery'),
            'sectionPartners' => SiteContent::section('home.partners'),
            'latestNews' => News::query()
                ->published()
                ->latest('published_at')
                ->take(8)
                ->get(),
            'homeEvents' => $this->homeEvents(),
            'homePublications' => Publication::query()
                ->published()
                ->orderByDesc('is_featured')
                ->orderByDesc('year')
                ->orderBy('title')
                ->take(4)
                ->get(),
            'homeGallery' => MediaItem::query()
                ->published()
                ->whereNotNull('image')
                ->orderBy('sort_order')
                ->latest('captured_at')
                ->take(8)
                ->get(),
            'publicStats' => PublicStats::snapshot(),
            'featuredResearch' => ResearchProject::query()->published()->where('is_featured', true)->orderByDesc('year')->take(3)->get()
                ->concat(Innovation::query()->published()->where('is_featured', true)->orderByDesc('year')->take(3)->get()),
        ]);
    }

    /**
     * Sorotan agenda di beranda mendahulukan kegiatan yang belum berlangsung.
     * Bila jumlahnya kurang dari tiga, sisanya diisi kegiatan terakhir supaya
     * bagian ini tidak pernah tampil kosong di luar musim kegiatan.
     */
    private function homeEvents(int $limit = 3): Collection
    {
        $events = Event::query()
            ->published()
            ->upcoming()
            ->orderBy('starts_at')
            ->take($limit)
            ->get();

        if ($events->count() >= $limit) {
            return $events;
        }

        return $events->concat(
            Event::query()
                ->published()
                ->past()
                ->latest('starts_at')
                ->take($limit - $events->count())
                ->get()
        );
    }
}
