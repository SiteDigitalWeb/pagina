<?php

Route::group(['middleware' => ['auth','administrador']], function (){


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


Route::post('/smtp/send-test', [SmtpConfigController::class, 'sendTestMail'])->name('smtp.send-test');

Route::prefix('sd')->group(function () {

Route::post('seo', 'Sitedigitalweb\Pagina\Http\ConfiguracionController@updateseo');
Route::get('seo', 'Sitedigitalweb\Pagina\Http\ConfiguracionController@seo');
Route::get('/smtp', [Sitedigitalweb\Pagina\Http\SmtpConfigController::class, 'index'])->name('smtp.index');
Route::post('/smtp', [Sitedigitalweb\Pagina\Http\SmtpConfigController::class, 'store'])->name('smtp.store');
Route::post('/smtp/test', [Sitedigitalweb\Pagina\Http\SmtpConfigController::class, 'sendTestMail'])->name('smtp.test');

Route::resource('country', \Sitedigitalweb\Pagina\Http\CountyController::class)->names('ge.embudo');
Route::resource('pages', 'Sitedigitalweb\Pagina\Http\PaginaController');
Route::get('create-page', 'Sitedigitalweb\Pagina\Http\PaginaController@show');
Route::get('create-subpage/{id}', 'Sitedigitalweb\Pagina\Http\PaginaController@show');
Route::post('create-page', 'Sitedigitalweb\Pagina\Http\PaginaController@create');
Route::get('pages/{id}/edit', 'Sitedigitalweb\Pagina\Http\PageController@edit')->name('pages.edit');
Route::put('pages/{id}', 'Sitedigitalweb\Pagina\Http\PageController@update')->name('pages.update');
Route::delete('pages/{id}', 'Sitedigitalweb\Pagina\Http\PageController@destroy')->name('pages.destroy');
Route::get('/editor', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'editor'])->name('editor');
Route::get('/preview/{id}', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'preview'])->name('preview');
Route::post('/templatess', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'store'])->name('templates.store');
Route::get('/templates/{id}', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'load'])->name('templates.load');
Route::get('/editor/components', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'getComponents']);
Route::post('/upload', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'upload'])->name('upload');
Route::resource('utm', Sitedigitalweb\Pagina\Http\UtmController::class);
Route::get('register-tenant', 'Sitedigitalweb\Pagina\Http\TenantController@register');
Route::post('create', 'Sitedigitalweb\Pagina\Http\TenantController@create');
Route::get('certificate', 'Sitedigitalweb\Pagina\Http\TenantController@certificate');
Route::post('generate-ssl', [Sitedigitalweb\Pagina\Http\TenantController::class, 'generate'])->name('generate.ssl');
Route::post('/tenants/ssl', [Sitedigitalweb\Pagina\Http\TenantController::class, 'createSSL'])->name('tenants.ssl');
Route::resource('recaptcha', Sitedigitalweb\Pagina\Http\RecaptchaSettingController::class);
Route::get('/grape-components', [Sitedigitalweb\Pagina\Http\TenantController::class, 'getGrapeComponents']);
Route::get('gestion/logo-head', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@logohead');
Route::get('components', [Sitedigitalweb\Pagina\Http\GrapejsController::class, 'getComponents'])->name('components.get');
Route::post('save-component', [Sitedigitalweb\Pagina\Http\GrapejsController::class, 'store'])->name('components.store');
Route::resource('configuration', 'Sitedigitalweb\Pagina\Http\ConfiguracionController');
Route::get('view-templates', 'Sitedigitalweb\Pagina\Http\ConfiguracionController@index');
Route::get('location', 'Sitedigitalweb\Pagina\Http\ConfiguracionController@verubicacion');
Route::get('cms_funel', 'Sitedigitalweb\Pagina\Http\GrapejsController@funel');
Route::get('menu', 'Sitedigitalweb\Pagina\Http\TemplateController@menu');
Route::get('popup', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'showForm'])->name('popup.form');
 Route::post('popup', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'theme'])->name('popup.store');
 Route::get('popup/data', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'getThemeData'])->name('popup.data');

 Route::get('recaptcha', 'Sitedigitalweb\Pagina\Http\ConfiguracionController@recaptcha');
Route::post('update-recaptcha', 'Sitedigitalweb\Pagina\Http\ConfiguracionController@updaterecaptcha');


Route::post('update-template', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'actualizarTemplate']);
Route::get('create-templates', 'Sitedigitalweb\Pagina\Http\TemplateController@creartemplate');

Route::post('save-template', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'templatestore'])->name('templates.stores');

Route::get('/templates/{id}/edit', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'edit'])->name('templates.edit');
Route::put('/templates/{id}', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'update'])->name('templates.update');
Route::get('templates', [Sitedigitalweb\Pagina\Http\GrapejsController::class, 'vistatemplates'])->name('sd.templates');
Route::resource('country', \Sitedigitalweb\Pagina\Http\CountryController::class)->names('sd.country');

Route::resource('city', \Sitedigitalweb\Pagina\Http\CityController::class)->names('sd.city');

});
});

// Ruta para servir el CSS del tenant actual
Route::get('/tenant-styles.css', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'getTenantCss']);

// Ruta para servir el CSS del sistema central
Route::get('/central-styles.css', function() {
    $cssPath = public_path('theme-central.css');
    if (File::exists($cssPath)) {
        return response(File::get($cssPath), 200)->header('Content-Type', 'text/css');
    }
    return response("/* CSS central no configurado */", 200)->header('Content-Type', 'text/css');
});


Route::get('/sd/themes.css', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'themeCss'])->name('theme.css');

Route::get('/generate-theme-css', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'generateThemeCss']);

Route::prefix('images')->group(function () {
    // Ruta para subir una o varias imágenes
    Route::post('/upload', ['Sitedigitalweb\Pagina\Http\ImageController', 'upload'])->name('images.upload');

    // Ruta para listar todas las imágenes guardadas
    Route::get('/list', ['Sitedigitalweb\Pagina\Http\ImageController', 'index'])->name('images.index');

    // Ruta para eliminar una imagen
    Route::delete('/delete', ['Sitedigitalweb\Pagina\Http\ImageController', 'destroy'])->name('images.destroy');
});


// File Manager Routes - RUTAS RELATIVAS
Route::prefix('file-manager')->group(function () {
    Route::get('/', [Sitedigitalweb\Pagina\Http\FileManagerController::class, 'index'])
         ->name('filemanager.index')
         ->middleware('web');
    
    Route::post('/upload', [Sitedigitalweb\Pagina\Http\FileManagerController::class, 'upload'])
         ->name('filemanager.upload');
         
    Route::post('/create-folder', [Sitedigitalweb\Pagina\Http\FileManagerController::class, 'createFolder'])
         ->name('filemanager.create-folder');
});











Route::get('pwa/offline', function() {
    return view('pwa.offline');
})->name('pwa.offline');

Route::get('pwa/install', function() {
    return view('pwa.install');
})->name('pwa.install');

// Para API que pueda necesitar el Service Worker
Route::get('/api/pwa/status', function() {
    return response()->json([
        'status' => 'ok',
        'version' => '1.0.0',
        'timestamp' => now()
    ]);
});

Route::get('pwa/pwa-check', function() {
    // Detectar navegador basado en User-Agent
    $userAgent = request()->header('User-Agent', '');
    $browser = 'Unknown';
    
    if (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edge') === false) {
        $browser = 'Chrome';
    } elseif (stripos($userAgent, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) {
        $browser = 'Safari';
    } elseif (stripos($userAgent, 'Edge') !== false) {
        $browser = 'Edge';
    } elseif (stripos($userAgent, 'Opera') !== false) {
        $browser = 'Opera';
    }
    
    // Verificar archivos
    $manifestExists = file_exists(public_path('manifest.json'));
    $iconsExist = file_exists(public_path('icons/icon-192x192.png'));
    $swExists = file_exists(public_path('sw.js'));
    $isHttps = request()->secure();
    $isLocal = app()->environment('local');
    
    // Calcular score
    $score = 0;
    $total = 4;
    if ($manifestExists) $score++;
    if ($swExists) $score++;
    if ($iconsExist) $score++;
    if ($isHttps || $isLocal) $score++;
    
    return response()->json([
        'status' => 'ok',
        'pwa' => [
            'supported' => in_array($browser, ['Chrome', 'Firefox', 'Safari', 'Edge']),
            'browser' => $browser,
            'requires_https' => $isHttps,
            'local_development' => $isLocal
        ],
        'files' => [
            'manifest' => [
                'exists' => $manifestExists,
                'path' => 'public/manifest.json',
                'size' => $manifestExists ? filesize(public_path('manifest.json')) : 0
            ],
            'service_worker' => [
                'exists' => $swExists,
                'path' => 'public/sw.js',
                'size' => $swExists ? filesize(public_path('sw.js')) : 0
            ],
            'icons' => [
                'exists' => $iconsExist,
                '192x192' => $iconsExist,
                '512x512' => file_exists(public_path('icons/icon-512x512.png'))
            ]
        ],
        'environment' => [
            'secure' => $isHttps,
            'env' => app()->environment(),
            'url' => config('app.url'),
            'host' => request()->getHost()
        ],
        'score' => [
            'current' => $score,
            'total' => $total,
            'percentage' => ($score / $total) * 100,
            'grade' => ($score / $total) * 100 >= 75 ? '✅ Good' : '⚠️ Needs Improvement'
        ],
        'instructions' => [
            '1. Verifica HTTPS: ' . ($isHttps ? '✅' : '❌'),
            '2. Archivo manifest.json: ' . ($manifestExists ? '✅' : '❌'),
            '3. Archivo sw.js: ' . ($swExists ? '✅' : '❌'),
            '4. Iconos: ' . ($iconsExist ? '✅' : '❌')
        ],
        'timestamp' => now()->toIso8601String()
    ]);
})->name('pwa.check');



Route::post('/push-subscribe', function (Illuminate\Http\Request $request) {
    $user = auth()->user();

    if (!$user) {
        return response()->json(['error' => 'Usuario no autenticado'], 401);
    }

    $data = $request->all();

    $user->updatePushSubscription(
        $data['endpoint'],
        $data['keys']['p256dh'],
        $data['keys']['auth']
    );

    return response()->json(['success' => true]);
});


Route::get('/manifest.json', [Sitedigitalweb\Pagina\Http\PwaManifestController::class, 'manifest'])->name('manifest.json');



// Rutas de administración
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    // Rutas del PWA Manifest - USANDO PARÁMETRO EXPLÍCITO
    Route::resource('pwa', Sitedigitalweb\Pagina\Http\PwaManifestController::class)->except(['show'])->parameters([
        'pwa' => 'pwaManifest'  // Mapear 'pwa' a 'pwaManifest'
    ]);
    
    // Ruta para activar/desactivar manifest
    Route::patch('/pwa/{pwaManifest}/toggle', [Sitedigitalweb\Pagina\Http\PwaManifestController::class, 'toggle'])
        ->name('pwa.toggle');
});


Route::group(['middleware' => ['auth','administrador']], function (){


Route::get('/gestor/ver-config/publico', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@publico');
Route::get('/gestor/ver-config/privado', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@privado');
Route::get('/gestor/planes-saas', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@planessaas');
Route::get('/gestor/editar-plan/{id}', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@editarplanessaas');
Route::post('suscripcion/editar-plan/{id}', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@actualizarplansaas');
Route::get('/suscripcion/pagos', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@pagos');
Route::get('/gestor/crear-plansaas', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@crearplanessaas');
Route::post('/suscripcion/crear-plan', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@crearplan');
Route::get('/suscripcion/eliminar-plan/{id}', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@eliminarplan');
Route::get('/suscripcion/ver-clientes', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@listaclientes');
Route::get('/suscripcion/ver-suscripciones', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@listasuscripciones');
Route::get('/suscripcion/credenciales', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@editarcredenciales');
Route::post('/suscripcion/editarcredenciales', 'DigitalsiteSaaS\Pagina\Http\SuscripcionController@editarcredencialesweb');
Route::post('/gestor/zip', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@template');
Route::get('/gestor/subir-template', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@templatevista');
Route::resource('gestor/templates/eliminartemplate', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@destroy');
Route::post('gestion/contenidos/crear-configuracion', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@crearconfiguracion');
Route::post('gestion/contenidos/actualizar-configuracion/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@update');
Route::get('gestion/venta', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@venta');
Route::post('gestion/contenidos/crearlogo', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@crearlogo');
Route::post('gestion/contenidos/crearlogofooter', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@crearlogofooter');
Route::post('gestion/contenidos/actualizarservicio', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@actualizarservicio');
Route::post('gestion/contenidos/actualizarrecaptcha', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@actualizarrecaptcha');
Route::post('gestion/contenidos/actualizarventa', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@actualizarventa');

Route::post('gestion/contenidos/redes-sociales', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@updatered');

Route::get('gestion/redes-sociales', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@verredes'); 

Route::get('gestion/seo', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@seo');
Route::get('gestion/pais-editar/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@paiseditar'); 
Route::post('gestion/editwhats/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@editarwhatsapp'); 
Route::get('gestion/whatsapp-editar/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@whatsappeditar'); 
Route::post('gestion/actualizarpais/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@actualizarpais');
Route::get('gestion/eliminarpais/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@eliminarpais');
Route::get('gestion/eliminarwhatsapp/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@eliminarwhatsapp');
Route::get('gestion/paises/importExport', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@importExportmun');
Route::get('gestion/paises/downloadExcel/{type}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@downloadExcelmun');
Route::post('gestion/paises/importExcel', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@importExcelmun');
Route::post('gestion/creardepartamento', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@creardepartamento'); 
Route::post('gestion/crearwhats', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@crearwhats'); 
Route::get('gestion/ubicacion/departamentos/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@departamentos');
Route::get('gestion/departamento-editar/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@departamentoeditar');
Route::post('gestion/actualizardepartamento/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@actualizardepartamento');
Route::get('gestion/eliminardepartamento/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@eliminardepartamento');
Route::get('gestion/crear-departamentos/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@creardepartamentos');
Route::get('gestion/crear-whatsapp', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@crearwhatsapp');
Route::get('gestion/departamentos/importExport', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@importExportdep');
Route::get('gestion/departamentos/downloadExcel/{type}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@downloadExceldep');
Route::post('gestion/departamentos/importExcel', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@importExceldep');
Route::get('gestion/municipios/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@municipios');
  Route::post('gestion/crearmunicipio/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@crearmunicipio');
  Route::get('gestion/municipio-editar/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@municipioeditar'); 
  Route::post('gestion/actualizarmunicipio/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@actualizarmunicipio');
  Route::get('gestion/eliminarmunicipio/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@eliminarmunicipio');

   Route::get('gestion/crear-municipio/{id}', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@creamunicipio');
 

 Route::get('gestion/whatsapp', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@configwhatsapp');



 Route::get('gestion/logo-footer', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@logofooter');


 
});











Route::group(['middleware' => ['web']], function (){

  Route::post('/tenants', [Sitedigitalweb\Pagina\Http\TenantController::class, 'store'])->name('tenants.store');
Route::post('/whatsapp-track', [DigitalsiteSaaS\Pagina\Http\WebController::class, 'trackClick'])->name('whatsapp.track');
Route::get('robots.txt', 'DigitalsiteSaaS\Pagina\Http\WebController@robot');

Route::post('/registroq', [DigitalsiteSaaS\Pagina\Http\WebController::class, 'submitForm'])->name('registros');
 Route::post('/cms/submit', 'Sitedigitalweb\Pagina\Http\WebController@submitForm');
  Route::post('/cms/registro', 'Sitedigitalweb\Pagina\Http\WebController@submitForm');

 Route::get('mensajes/estadisticas', 'Sitedigitalweb\Pagina\Http\WebController@estadistica');
  Route::get('/', 'Sitedigitalweb\Pagina\Http\TemplateController@page');
 Route::get('/{page}', 'Sitedigitalweb\Pagina\Http\TemplateController@pages');

 Route::get('/wen/csrf-token', function() {
    return response()->json(['token' => csrf_token()]);
});

});






