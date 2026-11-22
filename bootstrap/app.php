<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Http\Middleware\SetSecurityHeaders;
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
        $middleware->redirectGuestsTo(fn (): string => route('login'));
        $middleware->redirectUsersTo(function (): string {
            return auth()->user()?->isStaff()
                ? route('admin.dashboard')
                : route('account');
        });
        // Trust forwarded headers only from a proxy on this machine (cloudflared, or nginx on the same server),
        // so pages served through an HTTPS tunnel/proxy build https:// links and assets.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);
        $middleware->web(append: [
            EnsureUserIsNotBanned::class,
            SetSecurityHeaders::class,
        ]);
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhook/thanh-toan',
            'webhook/sepay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
