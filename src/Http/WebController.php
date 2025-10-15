<?php

 namespace Sitedigitalweb\Pagina\Http;
 use Sitedigitalweb\Pagina\Page;
 use Sitedigitalweb\Pagina\Cms_Stadistics;
 use Sitedigitalweb\Pagina\WhatsappClick;
 use Sitedigitalweb\Pagina\Cms_Forms;
 use Sitedigitalweb\Pagina\Cms_user;
 use Sitedigitalweb\Estadistica\Cms_Ips;
 use App\Models\RecaptchaSetting;
 use Mail;
 use DB;
 use Hash;
 use File;
 use Zipper;
 use Redirect;
 use Http;
 use App\Http\Controllers\Controller;
 use Input;
 use Illuminate\Support\Str;
 use Illuminate\Http\Request;
 use Validator;
 use Response;
 use Sitedigitalweb\Avanza\Avanzaempresa;
 use App\Http\Requests\FormularioFormRequest;
 use Auth;
 use Carbon\Carbon;
 use Hyn\Tenancy\Models\Hostname;
 use Hyn\Tenancy\Models\Website;
 use Hyn\Tenancy\Repositories\HostnameRepository;
 use Hyn\Tenancy\Repositories\WebsiteRepository;
 use GuzzleHttp\Client;
 use Sitedigitalweb\Pagina\Cms_Recaptcha;
 use App\Http\ConnectionsHelper;
 use URL;




class WebController extends Controller {

 protected $tenantName = null;

 public function __construct()
 {
  $this->middleware('web');
  $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();
  if ($hostname){
   $fqdn = $hostname->fqdn;
   $this->tenantName = explode(".", $fqdn)[0];
   }
  }
  

private function total(){
 $cart = session()->get('cart');
 $total = 0;
 if($cart == null){}
 else{
 foreach ($cart as $item) {
 $total += $item->precioinivafin * $item->quantity;
 }}
 return $total;
}

private function subtotal(){
 $cart = session()->get('cart');
 $subtotal = 0;
 if($cart == null){}
 else{
 foreach($cart as $item){
  $subtotal += $item->preciodescfin * $item->quantity;
 }}
 return $subtotal;
}



public function trackClick(Request $request) {
        try {

          if(!$this->tenantName){
            WhatsappClick::create([
                'slug' => $request->input('slug', 'Desconocido'),
                'utm_source' => $request->input('utm_source', 'Desconocido'),
                'utm_medium' => $request->input('utm_medium', 'Desconocido'),
                'utm_campaign' => $request->input('utm_campaign', 'Desconocido'),
                'medium' => $request->input('medium', 'Desconocido'),
            ]);
            }else{
              \Sitedigitalweb\Pagina\Tenant\WhatsappClick::create([
                'slug' => $request->input('slug', 'Desconocido'),
                'utm_source' => $request->input('utm_source', 'Desconocido'),
                'utm_medium' => $request->input('utm_medium', 'Desconocido'),
                'utm_campaign' => $request->input('utm_campaign', 'Desconocido'),
                'medium' => $request->input('medium', 'Desconocido'),
            ]);
            }

            return response()->json(['success' => 'Clic registrado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




   public function submitFormaa(Request $request)
{
     // Verificar si estamos en un tenant o en el sistema central
    if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
        $recaptcha = \Sitedigitalweb\Pagina\Tenant\Cms_Recaptcha::first();
    } else {
        // Entorno central (host)
        $recaptcha = Cms_Recaptcha::first();
    }
     if (!$recaptcha) {

        return back()->withErrors(['captcha' => 'Configuración de reCAPTCHA no encontrada']);
    }


    // 1. Validar reCAPTCHA antes que nada
    $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret' => $recaptcha->secret_key,
        'response' => $request->input('recaptcha_token'),
        'remoteip' => $request->ip(),
    ]);

    $score = $recaptchaResponse->json('score');
    $success = $recaptchaResponse->json('success');
    $action = $recaptchaResponse->json('action');

    if (!$success || $score < 0.5 || $action !== 'submit') {
        return back()->withErrors(['captcha' => 'reCAPTCHA falló la validación'])->withInput();
    }


    // 2. Validar campos del formulario
    $validated = $request->validate([
    'name'        => 'nullable|string|max:100',
    'last_name'   => 'nullable|string|max:100',
    'empresa'     => 'nullable|string|max:100',
    'address'     => 'nullable|string|max:255',
    'slug'        => 'nullable|string|max:100',
    'nit'         => 'nullable|string|max:100',
    'email'       => 'nullable|email|max:100',
    'phone'       => 'nullable|string|max:100',
    'interes'     => 'nullable|string|max:50',
    'mes'         => 'nullable|string|max:50',

    // FK obligatorias
    'sector_id'   => 'nullable|integer|',
    'cantidad_id' => 'nullable|integer|',
    'referido_id' => 'nullable|integer|',
    'country_id'  => 'nullable|integer|',
    'city_id'     => 'nullable|integer|',
    'tipo'        => 'nullable|integer',
    'funel_id'    => 'nullable|integer|',

    // UTM tracking
    'utm_source'   => 'nullable|string|max:100',
    'utm_medium'   => 'nullable|string|max:100',
    'utm_campaign' => 'nullable|string|max:100',

    // Texto largo
    'message' => 'nullable|string|max:500',

    // Campos adicionales
    'fecha' => 'nullable|string|max:50',

    // Campos dinámicos
    'campo1'  => 'nullable|string|max:255',
    'campo2'  => 'nullable|string|max:255',
    'campo3'  => 'nullable|string|max:255',
    'campo4'  => 'nullable|string|max:255',
    'campo5'  => 'nullable|string|max:255',
    'campo6'  => 'nullable|string|max:255',
    'campo7'  => 'nullable|string|max:255',
    'campo8'  => 'nullable|string|max:255',
    'campo9'  => 'nullable|string|max:255',
    'campo10' => 'nullable|string|max:255',
    'campo11' => 'nullable|string|max:255',
    'campo12' => 'nullable|string|max:255',
    'campo13' => 'nullable|string|max:255',
    'campo14' => 'nullable|string|max:255',
    'campo15' => 'nullable|string|max:255',
    'campo16' => 'nullable|string|max:255',
    'campo17' => 'nullable|string|max:255',
    'campo18' => 'nullable|string|max:255',
    'campo19' => 'nullable|string|max:255',
    'campo20' => 'nullable|string|max:255',
]);

    // 3. Asignar valores por defecto si vienen null
    $validated['sector_id']   = $validated['sector_id']   ?? 1;
    $validated['cantidad_id'] = $validated['cantidad_id'] ?? 1;
    $validated['referido_id'] = $validated['referido_id'] ?? 1;
    $validated['country_id']  = $validated['country_id']  ?? 1;
    $validated['city_id']     = $validated['city_id']     ?? 1;
    $validated['tipo']        = $validated['tipo']        ?? 1;
    $validated['funel_id']    = $validated['funel_id']    ?? 1;
    $validated['fecha']       = $validated['fecha']       ?? now()->format('Y-m-d');


     if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
        \Sitedigitalweb\Pagina\Tenant\Cms_user::create($validated);
    } else {
        // Entorno central (host)
        Cms_user::create($validated);
    }
       

    // 4. Redirigir con éxito
    return back()->with('success', '¡Formulario enviado correctamente!');
}



public function submitForm(Request $request)
{
    try {
        // 1. Verificar si estamos en un tenant o en el sistema central
        if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
            $recaptcha = \Sitedigitalweb\Pagina\Tenant\Cms_Recaptcha::first();
        } else {
            $recaptcha = Cms_Recaptcha::first();
        }

        if (!$recaptcha) {
            return response()->json([
                'success' => false,
                'error'   => 'Configuración de reCAPTCHA no encontrada'
            ], 422);
        }

        // 2. Validar reCAPTCHA
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => $recaptcha->secret_key,
            'response' => $request->input('recaptcha_token'),
            'remoteip' => $request->ip(),
        ]);

        $score   = $recaptchaResponse->json('score');
        $success = $recaptchaResponse->json('success');
        $action  = $recaptchaResponse->json('action');

        if (!$success || $score < 0.5 || $action !== 'submit') {
            return response()->json([
                'success' => false,
                'error'   => 'reCAPTCHA falló la validación'
            ], 422);
        }

        // 3. Validar campos
        $validated = $request->validate([
            'name'        => 'nullable|string|max:100',
            'last_name'   => 'nullable|string|max:100',
            'empresa'     => 'nullable|string|max:100',
            'address'     => 'nullable|string|max:255',
            'slug'        => 'nullable|string|max:100',
            'nit'         => 'nullable|string|max:100',
            'email'       => 'nullable|email|max:100',
            'phone'       => 'nullable|string|max:100',
            'interes'     => 'nullable|string|max:50',
            'mes'         => 'nullable|string|max:50',

            // FK
            'sector_id'   => 'nullable|integer|',
            'cantidad_id' => 'nullable|integer|',
            'referido_id' => 'nullable|integer|',
            'country_id'  => 'nullable|integer|',
            'city_id'     => 'nullable|integer|',
            'tipo'        => 'nullable|integer',
            'funel_id'    => 'nullable|integer|',

            // UTM
            'utm_source'   => 'nullable|string|max:100',
            'utm_medium'   => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',

            // Otros
            'message' => 'nullable|string|max:500',
            'fecha'   => 'nullable|string|max:50',

            // Campos dinámicos
            'campo1'  => 'nullable|string|max:255',
            'campo2'  => 'nullable|string|max:255',
            'campo3'  => 'nullable|string|max:255',
            'campo4'  => 'nullable|string|max:255',
            'campo5'  => 'nullable|string|max:255',
            'campo6'  => 'nullable|string|max:255',
            'campo7'  => 'nullable|string|max:255',
            'campo8'  => 'nullable|string|max:255',
            'campo9'  => 'nullable|string|max:255',
            'campo10' => 'nullable|string|max:255',
            'campo11' => 'nullable|string|max:255',
            'campo12' => 'nullable|string|max:255',
            'campo13' => 'nullable|string|max:255',
            'campo14' => 'nullable|string|max:255',
            'campo15' => 'nullable|string|max:255',
            'campo16' => 'nullable|string|max:255',
            'campo17' => 'nullable|string|max:255',
            'campo18' => 'nullable|string|max:255',
            'campo19' => 'nullable|string|max:255',
            'campo20' => 'nullable|string|max:255',
        ]);

        // 4. Valores por defecto si vienen null
        $validated['sector_id']   = $validated['sector_id']   ?? 1;
        $validated['cantidad_id'] = $validated['cantidad_id'] ?? 1;
        $validated['referido_id'] = $validated['referido_id'] ?? 1;
        $validated['country_id']  = $validated['country_id']  ?? 1;
        $validated['city_id']     = $validated['city_id']     ?? 1;
        $validated['tipo']        = $validated['tipo']        ?? 1;
        $validated['funel_id']    = $validated['funel_id']    ?? 1;
        $validated['fecha']       = $validated['fecha']       ?? now()->format('Y-m-d');

        // 5. Guardar en la BD según el tenant
        if ($website) {
            \Sitedigitalweb\Pagina\Tenant\Cms_user::create($validated);
        } else {
            Cms_user::create($validated);
        }

        // 6. Respuesta en JSON
        return back()->with('success', '¡Formulario enviado correctamente!');

    } catch (\Throwable $e) {
        \Log::error('Error en submitForm: '.$e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
        ], 500);
    }
}




public function estadistica()
{
    $ip = Input::get('ip');
    $tenant = $this->tenantName;

    $userModel = $tenant
        ? \Sitedigitalweb\Estadistica\Tenant\Cms_Ips::class
        : Cms_Ips::class;

    $user = $userModel::where('ip', $ip)->first();

    if (!$user) {
        $statsModel = $tenant
            ? \Sitedigitalweb\Pagina\Tenant\Cms_Stadistics::class
            : Cms_Stadistics::class;

        $pagina = new $statsModel();

        $pagina->fill([
            'ip'            => $ip,
            'host'          => Input::get('host'),
            'navegador'     => Input::get('navegador'),
            'referido'      => Input::get('referido'),
            'ciudad'        => Input::get('ciudad'),
            'pais'          => Input::get('pais'),
            'pagina'        => Input::get('pagina'),
            'mes'           => Input::get('mes'),
            'ano'           => Input::get('ano'),
            'hora'          => Input::get('hora'),
            'dia'           => Input::get('dia'),
            'idioma'        => Input::get('idioma'),
            'cp'            => Input::get('cp'),
            'longitud'      => Input::get('longitud'),
            'latitud'       => Input::get('latitud'),
            'fecha'         => Input::get('fecha'),
            'utm_medium'    => Input::get('utm_medium'),
            'utm_source'    => Input::get('utm_source'),
            'utm_campana'   => Input::get('utm_campana'),
            'remember_token'=> Input::get('_token'),
        ]);

        $pagina->save();

        return Redirect::to(Input::get('redireccion'))->with('status', 'ok_create');
    }

    // Si el usuario ya existe, puedes retornar algo si deseas.
}

  
  }