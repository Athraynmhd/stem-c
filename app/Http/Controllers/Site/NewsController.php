<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\News;
use App\Cms\Chrome\SiteContent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    use LoadsPageChrome;

    public function index(Request $request): View
    {
        $tag = trim((string) $request->query('tag'));
        $query = News::query()->published()->latest('published_at');

        if ($tag !== '') {
            $query->tagged($tag);
        }

        return view('site.news.index', [
            'news' => $query->paginate(9)->withQueryString(),
            'activeTag' => $tag,
            'tags' => News::tagTayang(),
            ...$this->pageChrome('berita'),
        ]);
    }

    public function show(string $slug): View
    {
        $item = News::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = News::query()
            ->published()
            ->where('id', '!=', $item->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('site.news.show', [
            'item' => $item,
            'related' => $related,
            'banner' => SiteContent::section('berita_detail.banner'),
        ]);
    }
}
