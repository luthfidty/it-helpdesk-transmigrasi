<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\userAkses;
use App\Http\Middleware\PreventBackHistory; // <--- TAMBAHAN 1

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'akses' => userAkses::class,
            'prevent-back-history' => PreventBackHistory::class // <--- TAMBAHAN 2
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();