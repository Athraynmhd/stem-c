<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\Publication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicationController extends Controller
{
    use LoadsPageChrome;

    public function index(Request $request): View
    {
        $type = $request->query('jenis');
        $year = $request->query('tahun');
        $penulis = trim((string) $request->query('penulis'));
        $kata = trim((string) $request->query('q'));

        $query = Publication::query()->published()->with('researchers');

        if ($type && array_key_exists($type, Publication::TYPES)) {
            $query->where('type', $type);
        }

        if ($year && ctype_digit((string) $year)) {
            $query->where('year', (int) $year);
        }

        if ($penulis !== '') {
            $query->where('authors', 'like', '%'.$penulis.'%');
        }

        if ($kata !== '') {
            $query->where(function ($inner) use ($kata) {
                $inner->where('title', 'like', '%'.$kata.'%')
                    ->orWhere('abstract', 'like', '%'.$kata.'%')
                    ->orWhere('title_en', 'like', '%'.$kata.'%');
            });
        }

        return view('site.publications.index', [
            'publications' => $query->orderByDesc('year')->orderBy('title')->paginate(10)->withQueryString(),
            'years' => Publication::query()->published()->distinct()->orderByDesc('year')->pluck('year'),
            'authors' => Publication::query()->published()->whereNotNull('authors')->orderBy('authors')->pluck('authors')->unique()->values(),
            'activeType' => $type,
            'activeYear' => $year,
            'activeAuthor' => $penulis,
            'activeQuery' => $kata,
            'totals' => Publication::query()->published()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
            ...$this->pageChrome('publikasi'),
        ]);
    }

    public function show(Publication $publication): View
    {
        abort_unless($publication->workflow_status === 'published', 404);

        $publication->load('researchers');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ScholarlyArticle',
            'headline' => $publication->localized('title'),
            'author' => array_map(
                fn (string $name) => ['@type' => 'Person', 'name' => trim($name)],
                array_values(array_filter(array_map('trim', explode(',', (string) $publication->authors)))),
            ),
            'datePublished' => (string) $publication->year,
            'url' => route('publikasi.show', $publication),
            'identifier' => $publication->doi,
        ];

        return view('site.publications.show', [
            'publication' => $publication,
            'schemaJson' => json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ...$this->pageChrome('publikasi'),
        ]);
    }
}
