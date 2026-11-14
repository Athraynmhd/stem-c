<?php

namespace App\Http\Controllers\Site;

use App\Cms\Chrome\SiteContent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    use LoadsPageChrome;

    public function index(Request $request): View
    {
        $category = $request->query('kategori');

        if ($category && ! array_key_exists($category, Event::CATEGORIES)) {
            $category = null;
        }

        $terapkanFilter = fn ($query) => $category
            ? $query->where('category', $category)
            : $query;

        $upcoming = $terapkanFilter(
            Event::query()->published()->upcoming()
        )->orderBy('starts_at')->get();

        $past = $terapkanFilter(
            Event::query()->published()->past()
        )->latest('starts_at')->paginate(6)->withQueryString();

        return view('site.events.index', [
            'upcoming' => $upcoming,
            'past' => $past,
            'activeCategory' => $category,
            // Hanya kategori yang benar-benar punya kegiatan tayang yang
            // ditawarkan, agar tidak ada tapis yang selalu kosong.
            'categories' => Event::query()
                ->published()
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->mapWithKeys(fn (string $key) => [$key => Event::CATEGORIES[$key] ?? $key])
                ->filter()
                ->sort(),
            ...$this->pageChrome('kegiatan'),
            'pastIntro' => SiteContent::section('kegiatan.past'),
        ]);
    }

    public function show(string $slug): View
    {
        $item = Event::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Event::query()
            ->published()
            ->where('id', '!=', $item->id)
            ->latest('starts_at')
            ->take(3)
            ->get();

        return view('site.events.show', [
            'item' => $item,
            'related' => $related,
            'banner' => SiteContent::section('kegiatan_detail.banner'),
        ]);
    }

    public function calendar(): View
    {
        $events = Event::query()->published()->orderBy('starts_at')->get();

        return view('site.events.calendar', [
            'events' => $events,
            ...$this->pageChrome('kegiatan'),
        ]);
    }

    public function call(): View
    {
        $items = Event::query()
            ->published()
            ->where('category', 'call-for-paper')
            ->orderByDesc('starts_at')
            ->paginate(10);

        return view('site.events.call', [
            'items' => $items,
            ...$this->pageChrome('kegiatan'),
        ]);
    }
}
