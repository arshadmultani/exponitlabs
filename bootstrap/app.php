<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Redirect unauthenticated MR field app visitors to /mr/login
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('mr*') || $request->is('api*')) {
                return route('mr.login');
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {

            // Ignore common non-critical exceptions
            if ($e instanceof AuthenticationException ||
                $e instanceof NotFoundHttpException) {
                return false;
            }

            DiscordAlert::message(implode("\n", [
                '🚨 **Error on exponit.com**',
                '**Type:** '.get_class($e),
                '**Message:** '.$e->getMessage(),
                '**File:** '.$e->getFile().':'.$e->getLine(),
                '**Time:** '.now()->format('d M Y, H:i:s'),
            ]));

        });
    })->create();
