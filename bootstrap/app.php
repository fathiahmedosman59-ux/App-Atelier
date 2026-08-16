<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\SessionTimeout::class,
        ]);

        $middleware->alias([
            'admin'    => \App\Http\Middleware\AdminOnly::class,
            'perm'     => \App\Http\Middleware\CheckPermission::class,
            'timeout'  => \App\Http\Middleware\SessionTimeout::class,
            'fournisseur.token' => \App\Http\Middleware\VerifyFournisseurToken::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
