<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Content\Innovation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InnovationController extends Controller
{
    use LoadsPageChrome;

    public function index(Request $request): View
    {
        $mode = $request->routeIs('riset.produk') ? 'produk' : 'hki';
        $type = $request->query('jenis');
        $query = Innovation::query()->published();

        if ($mode === 'produk') {
            $query->products();
        } else {
            $query->hki();
        }

        if ($type && array_key_exists($type, Innovation::TYPES)) {
            $query->where('type', $type);
        }

        return view('site.research.innovations', [
            'innovations' => $query->orderByDesc('year')->orderBy('title')->paginate(12)->withQueryString(),
            'mode' => $mode,
            'activeType' => $type,
            ...$this->pageChrome($mode === 'produk' ? 'produk' : 'hki'),
        ]);
    }

    public function show(string $slug): View
    {
        $item = Innovation::query()->published()->where('slug', $slug)->firstOrFail();
        $mode = $item->type === 'produk' ? 'produk' : 'hki';

        return view('site.research.innovation-show', [
            'item' => $item,
            'mode' => $mode,
            ...$this->pageChrome($mode),
        ]);
    }
}
