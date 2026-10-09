<?php

use App\Support\PageSize;
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
        // Render (and most PaaS hosts) terminate HTTPS at a load balancer.
        // Trusting the immediate proxy lets Laravel read X-Forwarded-Proto/For,
        // so it generates https:// URLs and sees the real client IP (used by
        // the login rate limiter). '*' trusts only the connecting peer, so
        // spoofed X-Forwarded-For entries added by clients are ignored.
        $middleware->trustProxies(at: '*');

        // Set by JavaScript (viewport size only), so it can't be encrypted.
        $middleware->encryptCookies(except: [PageSize::COOKIE]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
