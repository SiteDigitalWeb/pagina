<?php

 namespace DigitalsiteSaaS\Pagina\Http;
 use DigitalsiteSaaS\Pagina\Page;
 use DigitalsiteSaaS\Pagina\Estadistica;
 use DigitalsiteSaaS\Pagina\WhatsappClick;
 use DigitalsiteSaaS\Pagina\Forms;
  use DigitalsiteSaaS\Pagina\Ips;
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
 use DigitalsiteSaaS\Avanza\Avanzaempresa;
 use App\Http\Requests\FormularioFormRequest;
 use Auth;
 use Carbon\Carbon;
 use Hyn\Tenancy\Models\Hostname;
 use Hyn\Tenancy\Models\Website;
 use Hyn\Tenancy\Repositories\HostnameRepository;
 use Hyn\Tenancy\Repositories\WebsiteRepository;
 use GuzzleHttp\Client;
use DigitalsiteSaaS\Pagina\Cms_Recaptcha;
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
              \DigitalsiteSaaS\Pagina\Tenant\WhatsappClick::create([
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




   public function submitForm(Request $request)
{

     // Verificar si estamos en un tenant o en el sistema central
    if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
        $recaptcha = \DigitalsiteSaaS\Pagina\Tenant\Cms_Recaptcha::first();
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
        'name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'city' => 'nullable|string|max:20',
        'message' => 'nullable|string|max:500',
        'address' => 'nullable|string|max:255',
        // Campos dinámicos
        'campo1' => 'nullable|string|max:255',
        'campo2' => 'nullable|string|max:255',
        'campo3' => 'nullable|string|max:255',
        'campo4' => 'nullable|string|max:255',
        'campo5' => 'nullable|string|max:255',
        'campo6' => 'nullable|string|max:255',
        'campo7' => 'nullable|string|max:255',
        'campo8' => 'nullable|string|max:255',
        'campo9' => 'nullable|string|max:255',
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

    // 3. Guardar
    Forms::create($validated);

    // 4. Redirigir con éxito
    return back()->with('success', '¡Formulario enviado correctamente!');
}



  public function estadistica(){
    if(!$this->tenantName){
   $user = Ips::where('ip', Input::get('ip'))->first();
    }else{
    $user = \DigitalsiteSaaS\Pagina\Tenant\Ips::where('ip', Input::get('ip'))->first();
    } 
   if ($user){} else{
   if(!$this->tenantName){
   $pagina = new Estadistica;
   }else{
   $pagina = new \DigitalsiteSaaS\Pagina\Tenant\Estadistica;
   }
   $pagina->ip = Input::get('ip');
   $pagina->host = Input::get('host');
   $pagina->navegador = Input::get('navegador');
   $pagina->referido = Input::get('referido');
   $pagina->ciudad = Input::get('ciudad');
   $pagina->pais = Input::get('pais');
   $pagina->pagina = Input::get('pagina');
   $pagina->mes = Input::get('mes');
   $pagina->ano = Input::get('ano');
   $pagina->hora = Input::get('hora');
   $pagina->dia = Input::get('dia');
   $pagina->idioma = Input::get('idioma');
   $pagina->cp = Input::get('cp');
   $pagina->longitud = Input::get('longitud');
   $pagina->latitud = Input::get('latitud');
   $pagina->fecha = Input::get('fecha');
   $pagina->cp = Input::get('meses');
   $pagina->utm_medium = Input::get('utm_medium');
   $pagina->utm_source = Input::get('utm_source');
   $pagina->utm_campana = Input::get('utm_campana');
   $pagina->remember_token = Input::get('_token');
   $pagina->save();
     $redireccion = Input::get('redireccion');
     return Redirect::to($redireccion)->with('status', 'ok_create');
    }
   }
  }