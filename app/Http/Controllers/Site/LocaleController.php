<?php

namespace App\Http\Controllers\Site;

use App\Cms\Locale\SiteLocale;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SiteLocale::SUPPORTED, true), 404);
        SiteLocale::set($locale);

        return redirect()->back();
    }
}
