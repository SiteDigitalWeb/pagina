<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Forzar registro de rutas del paquete Pagina para tenants
            if (class_exists(\Sitedigitalweb\Pagina\PaginaServiceProvider::class)) {
                $provider = new \Sitedigitalweb\Pagina\PaginaServiceProvider(app());
                if (method_exists($provider, 'registerTenantRoutes')) {
                    $provider->registerTenantRoutes();
                }
            }

            if (class_exists(\Sitedigitalweb\Estadistica\EstadisticaServiceProvider::class)) {
                $provider = new \Sitedigitalweb\Pagina\PaginaServiceProvider(app());
                if (method_exists($provider, 'registerTenantRoutes')) {
                    $provider->registerTenantRoutes();
                }
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.admin' => \App\Http\Middleware\AuthenticateAdmin::class,
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'login',
            'register',
            'cms/submit',
            'cms/registro',
            'admin/login',
            'admin/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();