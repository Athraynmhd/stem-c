<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\Document;
use App\Models\Content\Event;
use App\Models\Content\Innovation;
use App\Models\Content\News;
use App\Models\Content\Page;
use App\Models\Content\Publication;
use App\Models\Content\Researcher;
use App\Models\Content\ResearchProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    use LoadsPageChrome;

    public function __invoke(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $results = collect();

        if (mb_strlen($q) >= 2) {
            $like = '%'.$q.'%';

            $this->ambil(Researcher::query()->published(), ['name', 'name_en', 'expertise', 'expertise_en', 'bio', 'bio_en', 'affiliation'], $like)
                ->each(fn ($item) => $results->push(['title' => $item->display_name, 'type' => 'Peneliti', 'url' => route('peneliti.show', $item->slug)]));

            $this->ambil(ResearchProject::query()->published(), ['title', 'title_en', 'abstract', 'abstract_en', 'leader', 'members'], $like)
                ->each(fn ($item) => $results->push(['title' => $item->localized('title'), 'type' => 'Penelitian', 'url' => route('riset.show', $item->slug)]));

            $this->ambil(Innovation::query()->published(), ['title', 'title_en', 'description', 'description_en', 'holders'], $like)
                ->each(fn ($item) => $results->push([
                    'title' => $item->localized('title'),
                    'type' => 'Inovasi',
                    'url' => route($item->type === 'produk' ? 'riset.produk.show' : 'riset.hki.show', $item->slug),
                ]));

            $this->ambil(Publication::query()->published(), ['title', 'title_en', 'authors', 'abstract', 'abstract_en', 'venue'], $like)
                ->each(fn ($item) => $results->push(['title' => $item->localized('title'), 'type' => 'Publikasi', 'url' => route('publikasi.show', $item)]));

            $this->ambil(News::query()->published(), ['title', 'title_en', 'excerpt', 'excerpt_en', 'body', 'body_en', 'tags'], $like)
                ->each(fn ($item) => $results->push(['title' => $item->localized('title'), 'type' => 'Berita', 'url' => route('berita.detail', $item->slug)]));

            $this->ambil(Event::query()->published(), ['title', 'title_en', 'excerpt', 'excerpt_en', 'body', 'body_en', 'location'], $like)
                ->each(fn ($item) => $results->push(['title' => $item->localized('title'), 'type' => 'Kegiatan', 'url' => route('kegiatan.detail', $item->slug)]));

            $this->ambil(Page::query()->published(), ['title', 'title_en', 'content', 'content_en'], $like)
                ->each(fn ($item) => $results->push(['title' => $item->localized('title'), 'type' => 'Profil', 'url' => route('profil.detail', $item->slug)]));

            $this->ambil(Document::query()->published(), ['title', 'title_en', 'description', 'description_en'], $like)
                ->each(fn ($item) => $results->push(['title' => $item->localized('title'), 'type' => 'Dokumen', 'url' => route('dokumen.index')]));
        }

        return view('site.search.index', [
            'q' => $q,
            'results' => $results,
            ...$this->pageChrome('cari'),
        ]);
    }

    private function ambil(Builder $query, array $fields, string $like)
    {
        return $query->where(function (Builder $inner) use ($fields, $like) {
            foreach ($fields as $i => $field) {
                $i === 0
                    ? $inner->where($field, 'like', $like)
                    : $inner->orWhere($field, 'like', $like);
            }
        })->limit(8)->get();
    }
}
