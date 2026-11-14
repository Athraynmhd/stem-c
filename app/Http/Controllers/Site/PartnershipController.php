<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use App\Models\Chrome\Partner;
use App\Models\Content\Partnership;
use Illuminate\View\View;

class PartnershipController extends Controller
{
    use LoadsPageChrome;

    public function index(): View
    {
        return view('site.research.partnerships', [
            'partnerships' => Partnership::query()->published()->orderByDesc('started_on')->paginate(12),
            ...$this->pageChrome('kemitraan'),
        ]);
    }

    public function partners(): View
    {
        return view('site.research.partners', [
            'partners' => Partner::query()->published()->get(),
            ...$this->pageChrome('mitra'),
        ]);
    }
}
