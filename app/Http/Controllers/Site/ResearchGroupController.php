<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\ResearchGroup;
use Illuminate\View\View;

class ResearchGroupController extends Controller
{
    use LoadsPageChrome;

    public function show(string $slug): View
    {
        $group = ResearchGroup::query()
            ->published()
            ->with(['researchers' => fn ($q) => $q->published()->orderBy('sort_order'), 'projects' => fn ($q) => $q->published()])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.research.group-show', [
            'group' => $group,
            ...$this->pageChrome('peneliti'),
        ]);
    }
}
