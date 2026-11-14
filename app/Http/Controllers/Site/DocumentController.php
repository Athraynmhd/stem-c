<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    use LoadsPageChrome;

    public function index(Request $request): View
    {
        $category = $request->query('kategori');

        if ($category && ! array_key_exists($category, Document::CATEGORIES)) {
            $category = null;
        }

        $query = Document::query()->published();

        if ($category) {
            $query->where('category', $category);
        }

        return view('site.documents.index', [
            'documents' => $query
                ->orderBy('sort_order')
                ->latest('published_on')
                ->paginate(15)
                ->withQueryString(),
            'categories' => Document::query()
                ->published()
                ->distinct()
                ->pluck('category')
                ->mapWithKeys(fn (string $key) => [$key => Document::CATEGORIES[$key] ?? $key])
                ->filter()
                ->sort(),
            'activeCategory' => $category,
            ...$this->pageChrome('dokumen'),
        ]);
    }
}
