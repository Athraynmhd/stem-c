<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\ResearchProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearchProjectController extends Controller
{
    use LoadsPageChrome;

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = ResearchProject::query()->published()->with(['researchers', 'group']);

        if ($status && array_key_exists($status, ResearchProject::STATUSES)) {
            $query->where('status', $status);
        }

        return view('site.research.projects', [
            'projects' => $query->orderByDesc('year')->orderBy('title')->paginate(10)->withQueryString(),
            'activeStatus' => $status,
            ...$this->pageChrome('riset'),
        ]);
    }

    public function show(string $slug): View
    {
        $project = ResearchProject::query()
            ->published()
            ->with(['researchers', 'group'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.research.project-show', [
            'project' => $project,
            ...$this->pageChrome('riset'),
        ]);
    }
}
