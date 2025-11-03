<?php

 namespace Sitedigitalweb\Pagina\Http;
 use Sitedigitalweb\Pagina\Page;
 use Sitedigitalweb\Pagina\Cms_Stadistics;
 use Sitedigitalweb\Pagina\WhatsappClick;
 use Sitedigitalweb\Pagina\Cms_Forms;
 use Sitedigitalweb\Pagina\Cms_user;
 use Sitedigitalweb\Estadistica\Cms_Ips;
 use Sitedigitalweb\Pagina\Cms_smtp_configs;
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




public function submitForm(Request $request)
{
    try {
        // 1. Verificar si estamos en un tenant o en el sistema central
        if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
            $recaptcha = \Sitedigitalweb\Pagina\Tenant\Cms_Recaptcha::first();
            $smtpConfig = \Sitedigitalweb\Pagina\Tenant\Cms_smtp_configs::first();
        } else {
            $recaptcha = Cms_Recaptcha::first();
            $smtpConfig = Cms_smtp_configs::first();
        }

        // Log para diagnóstico
        \Log::info('Iniciando submitForm', [
            'tenant' => $website ? 'yes' : 'no',
            'has_recaptcha' => $recaptcha ? 'yes' : 'no',
            'has_smtp' => $smtpConfig ? 'yes' : 'no',
            'email_destino' => $request->input('email_destino', 'no-email_destino-provided'),
            'sujeto' => $request->input('sujeto', 'no-sujeto-provided')
        ]);

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

        // 3. Validar campos (AJUSTADO con los nuevos nombres)
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

            // NUEVOS CAMPOS PARA EMAIL (AJUSTADOS)
            'email_destino' => 'nullable|email|max:100',  // Cambiado de 'to'
            'sujeto'        => 'nullable|string|max:200', // Cambiado de 'subject'
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

        \Log::info('Formulario guardado en BD', [
            'email' => $validated['email'] ?? 'no-email',
            'email_destino' => $validated['email_destino'] ?? 'no-email_destino',
            'sujeto' => $validated['sujeto'] ?? 'no-sujeto'
        ]);

        // 6. Configuración y verificación SMTP - SOLO ENVIAR SI HAY DESTINATARIO
        $mailSent = false;
        $mailError = null;
        
        // USAR email_destino EN LUGAR DE to
        $shouldSendEmail = !empty($validated['email_destino']);

        \Log::info('Verificación de envío de correo', [
            'should_send_email' => $shouldSendEmail,
            'email_destino_provided' => !empty($validated['email_destino']),
            'email_destino_value' => $validated['email_destino'] ?? 'empty'
        ]);

        if ($shouldSendEmail && $smtpConfig) {
            // Validar configuración SMTP mínima requerida
            if ($this->isValidSmtpConfig($smtpConfig)) {
                $mailFromName = trim(str_replace(['"', "'"], '', $smtpConfig->mail_from_name ?? 'Formulario Web'));
                
                // Log de configuración SMTP (sin password)
                \Log::info('Configurando SMTP para envío', [
                    'host' => $smtpConfig->mail_host,
                    'port' => $smtpConfig->mail_port,
                    'username' => $smtpConfig->mail_username,
                    'from_address' => $smtpConfig->mail_from_address,
                    'encryption' => $smtpConfig->mail_encryption,
                    'custom_email_destino' => $validated['email_destino'],
                    'custom_sujeto' => $validated['sujeto'] ?? 'not-set'
                ]);
                
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => $smtpConfig->mail_mailer ?? 'smtp',
                    'mail.mailers.smtp.host' => $smtpConfig->mail_host,
                    'mail.mailers.smtp.port' => $smtpConfig->mail_port,
                    'mail.mailers.smtp.username' => $smtpConfig->mail_username,
                    'mail.mailers.smtp.password' => $smtpConfig->mail_password,
                    'mail.mailers.smtp.encryption' => $smtpConfig->mail_encryption,
                    'mail.from.address' => $smtpConfig->mail_from_address,
                    'mail.from.name' => $mailFromName,
                ]);

                // 7. Enviar correo con verificación - SOLO SI HAY DESTINATARIO
                try {
                    \Log::info('Intentando enviar correo...');
                    
                    // USAR LOS CAMPOS DEL FORMULARIO CON NUEVOS NOMBRES
                    $emailDestino = $validated['email_destino'];
                    $sujeto = $validated['sujeto'] ?? '📩 Nuevo mensaje recibido desde el sitio web';
                    
                    \Log::info('Configuración de correo final:', [
                        'email_destino' => $emailDestino,
                        'sujeto' => $sujeto,
                        'from_email' => $validated['email'] ?? 'no-email',
                        'has_custom_sujeto' => !empty($validated['sujeto'])
                    ]);
                    
                    Mail::send('emails.contacto', ['data' => $validated], function ($message) use ($smtpConfig, $validated, $emailDestino, $sujeto) {
                        $message->to($emailDestino)->subject($sujeto);

                        // Copia al remitente si dejó su correo
                        if (!empty($validated['email'])) {
                            $message->cc($validated['email']);
                        }

                        // Responder a si hay un email del usuario
                        if (!empty($validated['email'])) {
                            $message->replyTo($validated['email']);
                        }

                        $message->from(
                            $smtpConfig->mail_from_address ?? 'no-reply@sitekonecta.com',
                            $smtpConfig->mail_from_name ?? 'Formulario Web'
                        );
                    });
                    
                    $mailSent = true;
                    \Log::info('Correo enviado exitosamente', [
                        'email_destino' => $emailDestino,
                        'sujeto' => $sujeto,
                        'from' => $smtpConfig->mail_from_address
                    ]);
                    
                } catch (\Exception $e) {
                    $mailError = $e->getMessage();
                    \Log::error('Error al enviar correo: ' . $mailError, [
                        'email_destino' => $emailDestino,
                        'sujeto' => $sujeto,
                        'config' => [
                            'host' => $smtpConfig->mail_host,
                            'port' => $smtpConfig->mail_port,
                            'username' => $smtpConfig->mail_username ? '***' : 'empty',
                            'from' => $smtpConfig->mail_from_address
                        ],
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                $mailError = 'Configuración SMTP incompleta';
                \Log::warning('Configuración SMTP incompleta', [
                    'smtpConfig' => [
                        'host' => $smtpConfig->mail_host ?? 'missing',
                        'port' => $smtpConfig->mail_port ?? 'missing',
                        'username' => $smtpConfig->mail_username ? '***' : 'missing',
                        'from_address' => $smtpConfig->mail_from_address ?? 'missing'
                    ]
                ]);
            }
        } else if (!$shouldSendEmail) {
            \Log::info('No se enviará correo: no se proporcionó email_destino');
            $mailError = 'No se proporcionó destinatario para el correo';
        } else {
            $mailError = 'No hay configuración SMTP disponible';
            \Log::warning('No se encontró configuración SMTP en la base de datos');
        }

        // 8. Respuesta informativa
        $successMessage = '✅ ¡Formulario enviado correctamente!';
        
        if ($mailSent) {
            $successMessage .= ' y correo notificado';
            
            \Log::info('Proceso completo: Formulario guardado y correo enviado', [
                'custom_email_used' => !empty($validated['email_destino']),
                'custom_sujeto_used' => !empty($validated['sujeto'])
            ]);
        } else if (!$shouldSendEmail) {
            // No mostrar error si no se configuró el email de notificación
            $successMessage .= '. Los datos han sido registrados correctamente';
            \Log::info('Formulario procesado sin envío de correo (no se configuró email_destino)');
        } else {
            $successMessage .= '. Los datos han sido registrados, pero el correo no pudo ser enviado';
            \Log::warning('Formulario enviado pero correo falló', [
                'error' => $mailError ?? 'Razón desconocida',
                'user_email' => $validated['email'] ?? 'no-email',
                'notification_email_destino' => $validated['email_destino'] ?? 'no-email_destino',
                'notification_sujeto' => $validated['sujeto'] ?? 'no-sujeto'
            ]);
        }

        return back()->with('success', $successMessage);

    } catch (\Throwable $e) {
        \Log::error('Error crítico en submitForm: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->except(['password', 'token']), // Excluir datos sensibles
            'email_destino_field' => $request->input('email_destino', 'not-provided'),
            'sujeto_field' => $request->input('sujeto', 'not-provided')
        ]);

        return response()->json([
            'success' => false,
            'error'   => 'Error interno del servidor. Por favor, intente nuevamente.'
        ], 500);
    }
}

// Método auxiliar para validar configuración SMTP (debes mantener este método)
private function isValidSmtpConfig($smtpConfig)
{
    return !empty($smtpConfig->mail_host) && 
           !empty($smtpConfig->mail_port) && 
           !empty($smtpConfig->mail_username) && 
           !empty($smtpConfig->mail_password) && 
           !empty($smtpConfig->mail_from_address);
}



public function testSmtpConfig()
{
    try {
        if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
            $smtpConfig = \Sitedigitalweb\Pagina\Tenant\Cms_smtp_configs::first();
        } else {
            $smtpConfig = Cms_smtp_configs::first();
        }

        if (!$smtpConfig) {
            return response()->json(['error' => 'No hay configuración SMTP'], 404);
        }

        \Log::info('SMTP Configuration Test', [
            'host' => $smtpConfig->mail_host,
            'port' => $smtpConfig->mail_port,
            'username' => $smtpConfig->mail_username,
            'from_address' => $smtpConfig->mail_from_address,
            'encryption' => $smtpConfig->mail_encryption,
            'has_password' => !empty($smtpConfig->mail_password)
        ]);

        // Configurar temporalmente
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $smtpConfig->mail_host,
            'mail.mailers.smtp.port' => $smtpConfig->mail_port,
            'mail.mailers.smtp.username' => $smtpConfig->mail_username,
            'mail.mailers.smtp.password' => $smtpConfig->mail_password,
            'mail.mailers.smtp.encryption' => $smtpConfig->mail_encryption,
        ]);

        // Test de conexión
        Mail::raw('Test de configuración SMTP', function ($message) use ($smtpConfig) {
            $message->to($smtpConfig->mail_from_address)
                    ->subject('Test SMTP');
        });

        return response()->json(['success' => 'SMTP configurado correctamente']);

    } catch (\Exception $e) {
        \Log::error('SMTP Test Failed: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
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