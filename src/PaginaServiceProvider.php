<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class PaginaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('pagina', function ($app) {
            return new Pagina;
        });
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/Http/helpers.php')) {
            require_once __DIR__ . '/Http/helpers.php';
        }

        $this->loadViewsFrom(__DIR__ . '/../views', 'pagina');

        $this->publishes([
            __DIR__ . '/migrations/' => database_path('migrations/tenant'),
        ], 'pagina-migrations');

        $this->app->booted(function () {
            $this->registerRoutes();
        });
    }

    protected function registerRoutes(): void
    {
        // Solo rutas centrales — las tenant se registran desde web.php
        Route::middleware(['web'])->group(function () {
            $this->centralRoutes();
        });
    }

    public function registerTenantRoutes(): void
    {
        Route::middleware([
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
        ])->group(function () {
            $this->tenantRoutes();
        });
    }

    public function tenantRoutes(): void
    {
      
        Route::get('users/import', [Sitedigitalweb\Pagina\Http\WebController::class, 'showImportForm'])->name('users.show.import');
Route::post('users/import', [Sitedigitalweb\Pagina\Http\WebController::class, 'import'])->name('users.import');
Route::get('users/export', [Sitedigitalweb\Pagina\Http\WebController::class, 'exportUsers'])->name('users.export');
Route::get('cms-users/import', [Sitedigitalweb\Pagina\Http\WebController::class, 'showCmsImportForm'])
         ->name('cms.users.show.import');
Route::post('cms-users/import', [Sitedigitalweb\Pagina\Http\WebController::class, 'importCmsUsers'])
         ->name('cms.users.import');
Route::get('cms-users/export', [Sitedigitalweb\Pagina\Http\WebController::class, 'exportCmsUsers'])
         ->name('cms.users.export');
Route::get('cms-users/export/template', [Sitedigitalweb\Pagina\Http\WebController::class, 'downloadCmsTemplate'])->name('cms.users.export.template');
      
        // ── Públicas ──────────────────────────────────────────
        // En routes/web.php — cambiar la ruta raíz del tenant
         Route::get('/', [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'page'])
         ->defaults('page', null);
        Route::get('/tenant-styles.css', [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'getTenantCss']);
        Route::get('/sd/themes.css', [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'themeCss'])->name('theme.css');
        Route::post('/cms/submit',   [\Sitedigitalweb\Pagina\Http\WebController::class, 'submitForm']);
        Route::post('/cms/registro', [\Sitedigitalweb\Pagina\Http\WebController::class, 'submitForm']);
        Route::post('/mensajes/estadisticas', [\Sitedigitalweb\Pagina\Http\WebController::class, 'estadistica']);

        // Push notifications
        Route::prefix('api')->group(function () {
            Route::post('/push/subscribe',   [\Sitedigitalweb\Pagina\Http\PushSubscriptionController::class, 'subscribe']);
            Route::post('/push/unsubscribe', [\Sitedigitalweb\Pagina\Http\PushSubscriptionController::class, 'unsubscribe']);
        });

        // ── AI — comentadas hasta que existan los controllers ──
         Route::post('/sd/ai/template', [\Sitedigitalweb\Pagina\Http\AiTemplateController::class, 'generate'])->name('ai.template.generate');
         Route::post('/sd/ai/generate', [\Sitedigitalweb\Pagina\Http\AIController::class, 'generate'])->name('ai.generate');
         Route::post('/sd/ai/section',  [\Sitedigitalweb\Pagina\Http\AiSectionController::class, 'generate'])->name('ai.section.generate');

        // CSRF público
        Route::get('/wen/csrf-token', fn() => response()->json(['token' => csrf_token()]));

        // ── Protegidas ────────────────────────────────────────
        Route::middleware(['auth'])->group(function () {

            // Import/Export usuarios
            Route::get('/users/import',  [\Sitedigitalweb\Pagina\Http\WebController::class, 'showImportForm'])->name('users.show.import');
            Route::post('/users/import', [\Sitedigitalweb\Pagina\Http\WebController::class, 'import'])->name('users.import');
            Route::get('/users/export',  [\Sitedigitalweb\Pagina\Http\WebController::class, 'exportUsers'])->name('users.export');

            // SMTP
            Route::post('/smtp/send-test', [\Sitedigitalweb\Pagina\Http\SmtpConfigController::class, 'sendTestMail'])->name('smtp.send-test');

            // File Manager
            Route::prefix('file-manager')->group(function () {
                Route::get('/',               [\Sitedigitalweb\Pagina\Http\FileManagerController::class, 'index'])->name('filemanager.index');
                Route::post('/upload',        [\Sitedigitalweb\Pagina\Http\FileManagerController::class, 'upload'])->name('filemanager.upload');
                Route::post('/create-folder', [\Sitedigitalweb\Pagina\Http\FileManagerController::class, 'createFolder'])->name('filemanager.create-folder');
            });

            // Imágenes
            Route::prefix('images')->group(function () {
                Route::post('/upload',   [\Sitedigitalweb\Pagina\Http\ImageController::class, 'upload'])->name('images.upload');
                Route::get('/list',      [\Sitedigitalweb\Pagina\Http\ImageController::class, 'index'])->name('images.index');
                Route::delete('/delete', [\Sitedigitalweb\Pagina\Http\ImageController::class, 'destroy'])->name('images.destroy');
            });

            // ── Prefijo /sd ───────────────────────────────────
            Route::prefix('sd')->group(function () {

                // SEO
                Route::get('/seo',  [\Sitedigitalweb\Pagina\Http\ConfiguracionController::class, 'seo']);
                Route::post('/seo', [\Sitedigitalweb\Pagina\Http\ConfiguracionController::class, 'updateseo']);

                // SMTP
                Route::get('/smtp',       [\Sitedigitalweb\Pagina\Http\SmtpConfigController::class, 'index'])->name('smtp.index');
                Route::post('/smtp',      [\Sitedigitalweb\Pagina\Http\SmtpConfigController::class, 'store'])->name('smtp.store');
                Route::post('/smtp/test', [\Sitedigitalweb\Pagina\Http\SmtpConfigController::class, 'sendTestMail'])->name('smtp.test');

                // Resources
                Route::resource('pages',        \Sitedigitalweb\Pagina\Http\PaginaController::class);
                Route::resource('country',       \Sitedigitalweb\Pagina\Http\CountryController::class)->names('sd.country');
                Route::resource('city',          \Sitedigitalweb\Pagina\Http\CityController::class)->names('sd.city');
                Route::resource('utm',           \Sitedigitalweb\Pagina\Http\UtmController::class);
                Route::resource('recaptcha',     \Sitedigitalweb\Pagina\Http\RecaptchaSettingController::class);
                Route::resource('configuration', \Sitedigitalweb\Pagina\Http\ConfiguracionController::class);

                // Editor GrapesJS
                Route::get('/editor',            [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'editor'])->name('editor');
                Route::get('/preview/{id}',      [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'preview'])->name('preview');
                Route::post('/templatess',       [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'store'])->name('templates.store');
                Route::get('/templates/{id}',    [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'load'])->name('templates.load');
                Route::get('/editor/components', [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'getComponents']);
                Route::post('/upload',           [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'upload'])->name('upload');
                Route::get('/grape-components',  [\Sitedigitalweb\Pagina\Http\TenantController::class, 'getGrapeComponents']);
                Route::get('/components',        [\Sitedigitalweb\Pagina\Http\GrapejsController::class, 'getComponents'])->name('components.get');
                Route::post('/save-component',   [\Sitedigitalweb\Pagina\Http\GrapejsController::class, 'store'])->name('components.store');
                Route::get('/cms_funel',         [\Sitedigitalweb\Pagina\Http\GrapejsController::class, 'funel']);
                Route::get('/templates',         [\Sitedigitalweb\Pagina\Http\GrapejsController::class, 'vistatemplates'])->name('sd.templates');

                // Páginas
                Route::get('/create-page',          [\Sitedigitalweb\Pagina\Http\PaginaController::class, 'show']);
                Route::post('/create-page',         [\Sitedigitalweb\Pagina\Http\PaginaController::class, 'store']);
                Route::get('/create-subpage/{id}',  [\Sitedigitalweb\Pagina\Http\PaginaController::class, 'show']);
                Route::get('/pages/{id}/edit',      [\Sitedigitalweb\Pagina\Http\PaginaController::class, 'edit'])->name('pages.edit');
                Route::put('/pages/{id}',           [\Sitedigitalweb\Pagina\Http\PaginaController::class, 'update'])->name('pages.update');
                Route::delete('/pages/{id}',        [\Sitedigitalweb\Pagina\Http\PaginaController::class, 'destroy'])->name('pages.destroy');
                 Route::get('/certificate',     [\Sitedigitalweb\Pagina\Http\TenantController::class, 'certificate']);
                // Popups
                Route::get('/popup',      [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'showForm'])->name('popup.form');
                Route::post('/popup',     [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'theme'])->name('popup.store');
                Route::get('/popup/data', [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'getThemeData'])->name('popup.data');

                // Plantillas
                Route::post('/update-template',    [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'actualizarTemplate']);
                Route::get('/create-templates',    [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'creartemplate']);
                Route::post('/save-template',      [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'templatestore'])->name('templates.stores');
                Route::get('/templates/{id}/edit', [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'edit'])->name('templates.edit');
                Route::put('/templates/{id}',      [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'update'])->name('templates.update');

                // Departamentos y Recaptcha
                Route::get('/departamentos/{id}', [\Sitedigitalweb\Pagina\Http\ConfiguracionController::class, 'departamentos']);
                Route::get('/recaptcha',          [\Sitedigitalweb\Pagina\Http\ConfiguracionController::class, 'recaptcha']);
                Route::post('/update-recaptcha',  [\Sitedigitalweb\Pagina\Http\ConfiguracionController::class, 'updaterecaptcha']);
                Route::get('/generate-theme-css', [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'generateThemeCss']);
            });

            // ⚠️ Esta ruta al final siempre — captura cualquier /{page}
            Route::get('/{page}', [\Sitedigitalweb\Pagina\Http\TemplateController::class, 'pages']);
        });
    }

    protected function centralRoutes(): void
    {
        Route::middleware(['auth.admin'])->group(function () {
            Route::get('/sd/register-tenant', [\Sitedigitalweb\Pagina\Http\TenantController::class, 'register']);
            Route::post('/sd/create',         [\Sitedigitalweb\Pagina\Http\TenantController::class, 'create']);
           
            Route::post('/sd/generate-ssl',   [\Sitedigitalweb\Pagina\Http\TenantController::class, 'store'])->name('generate.ssl');
        });
    }
}