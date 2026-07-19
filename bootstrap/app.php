<?php

if (!getenv('OPENSSL_CONF') && file_exists('C:/Users/eykel/.gemini/antigravity/scratch/php83/extras/ssl/openssl.cnf')) {
    putenv('OPENSSL_CONF=C:/Users/eykel/.gemini/antigravity/scratch/php83/extras/ssl/openssl.cnf');
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
