<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\LoadsPageChrome;
use Illuminate\View\View;

class PrivacyController extends Controller
{
    use LoadsPageChrome;

    public function __invoke(): View
    {
        return view('site.privacy.index', $this->pageChrome('privasi'));
    }
}
