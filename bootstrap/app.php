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
    ->withMiddleware(function (Middleware $middleware) {
        // AÑADE ESTAS LÍNEAS PARA LOS MIDDLEWARES DE SPATIE/LARAVEL-PERMISSION
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Opcional: Si estás usando Sanctum para SPAs con autenticación basada en cookies
        // y tu frontend y backend están en dominios diferentes (o diferentes puertos en localhost),
        // podrías necesitar asegurar que ciertos middlewares de Sanctum estén en el grupo 'api'.
        // Sin embargo, con tokens API (Bearer tokens), esto es menos crítico aquí.
        // $middleware->statefulApi(); // Si usas Sanctum para autenticación de SPA con cookies.

        // También, Laravel 11+ maneja los grupos de middleware como 'api' y 'web'
        // de forma un poco diferente. El `Kernel.php` es más ligero.
        // El middleware 'auth:sanctum' que usamos en routes/api.php debería funcionar
        // siempre que Sanctum esté bien configurado.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();