<?php

Route::group(['middleware' => ['auth','administrador']], function (){
Route::prefix('sd')->group(function () {
Route::resource('pages', 'Sitedigitalweb\Pagina\Http\PaginaController');
Route::get('create-page', 'Sitedigitalweb\Pagina\Http\PaginaController@show');
Route::get('create-subpage/{id}', 'Sitedigitalweb\Pagina\Http\PaginaController@show');
Route::post('create-page', 'Sitedigitalweb\Pagina\Http\PaginaController@create');
Route::get('pages/{id}/edit', 'PageController@edit')->name('pages.edit');
Route::put('pages/{id}', 'PageController@update')->name('pages.update');
Route::delete('pages/{id}', 'PageController@destroy')->name('pages.destroy');
Route::get('/editor', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'editor'])->name('editor');
Route::get('/preview/{id}', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'preview'])->name('preview');
Route::post('/templatess', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'store'])->name('templates.store');
Route::get('/templates/{id}', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'load'])->name('templates.load');
Route::get('/editor/components', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'getComponents']);
Route::post('/upload', [Sitedigitalweb\Pagina\Http\TemplateController::class, 'upload'])->name('upload');

Route::get('/grape-components', function () {
  $template = 'buyer';
  $componentsPath = resource_path('views/'.$template);
  $files = glob($componentsPath . '/*.blade.php');
  $components = [];
  foreach ($files as $file) {
   $name = basename($file, '.blade.php');
   $html = view("buyer.$name")->render();
   $components[] = [
    'id' => $name,
    'label' => ucfirst(str_replace('-', ' ', $name)),
    'content' => $html,
    'category' => 'Mis Componentes'
   ];
  }
  return response()->json($components); 
 });

 Route::get('gestion/logo-head', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@logohead');


Route::get('components', [DigitalsiteSaaS\Pagina\Http\GrapejsController::class, 'getComponents'])->name('components.get');

Route::post('save-component', [DigitalsiteSaaS\Pagina\Http\GrapejsController::class, 'store'])->name('components.store');


});
});

 Route::get('/', 'Sitedigitalweb\Pagina\Http\TemplateController@page');
 Route::get('/{id}', 'Sitedigitalweb\Pagina\Http\TemplateController@pages');



Route::prefix('images')->group(function () {
    // Ruta para subir una o varias imágenes
    Route::post('/upload', ['App\Http\Controllers\ImageController', 'upload'])->name('images.upload');

    // Ruta para listar todas las imágenes guardadas
    Route::get('/list', ['App\Http\Controllers\ImageController', 'index'])->name('images.index');

    // Ruta para eliminar una imagen
    Route::delete('/delete', ['App\Http\Controllers\ImageController', 'destroy'])->name('images.destroy');
});



Route::group(['middleware' => ['auth','administrador']], function (){
Route::resource('/gestor/ver-templates', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController');

 Route::get('sd/register-tenant', 'DigitalsiteSaaS\Pagina\Http\TenantController@register');
 Route::post('sd/create', 'DigitalsiteSaaS\Pagina\Http\TenantController@create');
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
Route::post('gestion/contenidos/seoupdate', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@updateseo');
Route::post('gestion/contenidos/redes-sociales', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@updatered');
Route::get('gestion/recaptcha', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@recaptcha');
Route::get('gestion/redes-sociales', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@verredes'); 
Route::get('gestion/ubicacion', 'DigitalsiteSaaS\Pagina\Http\ConfiguracionController@verubicacion');
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
Route::post('/whatsapp-track', [DigitalsiteSaaS\Pagina\Http\WebController::class, 'trackClick'])->name('whatsapp.track');
Route::get('robots.txt', 'DigitalsiteSaaS\Pagina\Http\WebController@robot');

Route::post('/registroq', [DigitalsiteSaaS\Pagina\Http\WebController::class, 'submitForm'])->name('registros');
 Route::post('cms/registro', 'Sitedigitalweb\Pagina\Http\WebController@submitForm');

 Route::get('mensajes/estadisticas', 'DigitalsiteSaaS\Pagina\Http\WebController@estadistica');
});




