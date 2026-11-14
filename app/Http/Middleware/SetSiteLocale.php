<?php

namespace App\Http\Middleware;

use App\Cms\Locale\SiteLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSiteLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(SiteLocale::current());

        return $next($request);
    }
}
