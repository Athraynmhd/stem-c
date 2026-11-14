<?php

use App\Http\Middleware\SetProductionSecurityHeaders;
use App\Http\Middleware\SetSiteLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SetProductionSecurityHeaders::class);
        $middleware->appendToGroup('web', SetSiteLocale::class);

        // Satu-satunya pintu login adalah panel Filament; tanpa ini middleware
        // auth mencari route bernama "login" yang tidak pernah didaftarkan.
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
