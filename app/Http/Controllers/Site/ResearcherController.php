<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\Researcher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearcherController extends Controller
{
    use LoadsPageChrome;

    public function index(Request $request): View
    {
        $group = $request->query('kelompok');
        $query = Researcher::query()->published()->with('group');

        if ($group && array_key_exists($group, Researcher::GROUPS)) {
            $query->where(function ($inner) use ($group) {
                $inner->where('research_group', $group)
                    ->orWhereHas('group', fn ($g) => $g->where('code', $group)->orWhere('slug', $group));
            });
        }

        return view('site.researchers.index', [
            'researchers' => $query->orderBy('sort_order')->orderBy('name')->paginate(12)->withQueryString(),
            'activeGroup' => $group,
            ...$this->pageChrome('peneliti'),
        ]);
    }

    public function show(string $slug): View
    {
        $researcher = Researcher::query()
            ->published()
            ->with(['group', 'publications' => fn ($q) => $q->published(), 'projects' => fn ($q) => $q->published()])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.researchers.show', [
            'researcher' => $researcher,
            ...$this->pageChrome('peneliti'),
        ]);
    }
}
