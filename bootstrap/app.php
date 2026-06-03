<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // There is no standalone "login" route — Filament owns auth at "/".
        // Send unauthenticated visitors there instead of a missing named route.
        $middleware->redirectGuestsTo(fn () => '/');
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
