<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Content\Document;
use App\Models\Content\Event;
use App\Models\Content\Innovation;
use App\Models\Content\News;
use App\Models\Content\Page;
use App\Models\Content\Publication;
use App\Models\Content\Researcher;
use App\Models\Content\ResearchGroup;
use App\Models\Content\ResearchProject;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            route('home'),
            route('profil'),
            route('peneliti.index'),
            route('riset.index'),
            route('riset.produk'),
            route('riset.hki'),
            route('publikasi.index'),
            route('kegiatan.index'),
            route('kegiatan.kalender'),
            route('kegiatan.call'),
            route('berita.index'),
            route('dokumentasi.index'),
            route('dokumen.index'),
            route('kerja-sama.mitra'),
            route('kerja-sama.mou'),
            route('kerja-sama.ajukan'),
            route('kontak'),
            route('privasi'),
            route('cari'),
        ]);

        Page::query()->published()->pluck('slug')->each(fn ($slug) => $urls->push(route('profil.detail', $slug)));
        News::query()->published()->pluck('slug')->each(fn ($slug) => $urls->push(route('berita.detail', $slug)));
        Event::query()->published()->pluck('slug')->each(fn ($slug) => $urls->push(route('kegiatan.detail', $slug)));
        Researcher::query()->published()->pluck('slug')->each(fn ($slug) => $urls->push(route('peneliti.show', $slug)));
        ResearchGroup::query()->published()->pluck('slug')->each(fn ($slug) => $urls->push(route('kelompok.show', $slug)));
        ResearchProject::query()->published()->pluck('slug')->each(fn ($slug) => $urls->push(route('riset.show', $slug)));
        Innovation::query()->published()->each(function (Innovation $item) use ($urls) {
            $urls->push(route($item->type === 'produk' ? 'riset.produk.show' : 'riset.hki.show', $item->slug));
        });
        Publication::query()->published()->each(fn (Publication $item) => $urls->push(route('publikasi.show', $item)));
        Document::query()->published()->each(fn () => null);

        $xml = view('site.sitemap', ['urls' => $urls->unique()->values()])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
