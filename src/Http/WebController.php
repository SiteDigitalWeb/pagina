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
use GuzzleHttp\Client;
use Sitedigitalweb\Pagina\Cms_Recaptcha;
use App\Http\ConnectionsHelper;
use URL;
use App\Imports\CmsUsersImport;
use App\Exports\CmsUsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Log;

class WebController extends Controller
{
    protected $tenant = null;
    protected $tenantId = null;

    public function __construct()
    {
        // Inicializar tenant si estamos en contexto multi-tenant
        if (tenancy()->initialized) {
            $this->tenant = tenant();
            $this->tenantId = $this->tenant->id;
        }
    }

    /**
     * Determina si la petición está dentro de un tenant.
     */
    protected function isTenantContext()
    {
        return tenancy()->initialized;
    }

    /**
     * Obtiene el ID del tenant actual o null.
     */
    protected function getTenantId()
    {
        return $this->tenantId;
    }

    // ====================== MÉTODOS PRIVADOS AUXILIARES ======================
    private function total()
    {
        $cart = session()->get('cart');
        $total = 0;
        if ($cart) {
            foreach ($cart as $item) {
                $total += $item->precioinivafin * $item->quantity;
            }
        }
        return $total;
    }

    private function subtotal()
    {
        $cart = session()->get('cart');
        $subtotal = 0;
        if ($cart) {
            foreach ($cart as $item) {
                $subtotal += $item->preciodescfin * $item->quantity;
            }
        }
        return $subtotal;
    }

    /**
     * Valida que la configuración SMTP tenga los campos obligatorios.
     */
    private function isValidSmtpConfig($smtpConfig)
    {
        return !empty($smtpConfig->mail_host) &&
               !empty($smtpConfig->mail_port) &&
               !empty($smtpConfig->mail_username) &&
               !empty($smtpConfig->mail_password) &&
               !empty($smtpConfig->mail_from_address);
    }

    // ====================== MÉTODOS PÚBLICOS ======================

    public function trackClick(Request $request)
    {
        try {
            if ($this->isTenantContext()) {
                \Sitedigitalweb\Pagina\Tenant\WhatsappClick::create([
                    'slug' => $request->input('slug', 'Desconocido'),
                    'utm_source' => $request->input('utm_source', 'Desconocido'),
                    'utm_medium' => $request->input('utm_medium', 'Desconocido'),
                    'utm_campaign' => $request->input('utm_campaign', 'Desconocido'),
                    'medium' => $request->input('medium', 'Desconocido'),
                ]);
            } else {
                WhatsappClick::create([
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
            $isTenant = $this->isTenantContext();

            // Obtener configuración según contexto
            if ($isTenant) {
                $recaptcha = \Sitedigitalweb\Pagina\Tenant\Cms_Recaptcha::first();
                $smtpConfig = \Sitedigitalweb\Pagina\Tenant\Cms_smtp_configs::first();
            } else {
                $recaptcha = Cms_Recaptcha::first();
                $smtpConfig = Cms_smtp_configs::first();
            }

            \Log::info('Iniciando submitForm', [
                'tenant' => $isTenant ? 'yes' : 'no',
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

            // Validar reCAPTCHA
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

            // Validar campos del formulario
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
                'sector_id'   => 'nullable|integer',
                'cantidad_id' => 'nullable|integer',
                'referido_id' => 'nullable|integer',
                'country_id'  => 'nullable|integer',
                'city_id'     => 'nullable|integer',
                'tipo'        => 'nullable|integer',
                'funel_id'    => 'nullable|integer',
                'utm_source'   => 'nullable|string|max:100',
                'utm_medium'   => 'nullable|string|max:100',
                'utm_campaign' => 'nullable|string|max:100',
                'message' => 'nullable|string|max:500',
                'fecha'   => 'nullable|string|max:50',
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
                'email_destino' => 'nullable|email|max:100',
                'sujeto'        => 'nullable|string|max:200',
            ]);

            // Valores por defecto
            $validated['sector_id']   = $validated['sector_id']   ?? 1;
            $validated['cantidad_id'] = $validated['cantidad_id'] ?? 1;
            $validated['referido_id'] = $validated['referido_id'] ?? 1;
            $validated['country_id']  = $validated['country_id']  ?? 1;
            $validated['city_id']     = $validated['city_id']     ?? 1;
            $validated['tipo']        = $validated['tipo']        ?? 1;
            $validated['funel_id']    = $validated['funel_id']    ?? 1;
            $validated['fecha']       = $validated['fecha']       ?? now()->format('Y-m-d');

            // Guardar en BD según contexto
            if ($isTenant) {
                \Sitedigitalweb\Pagina\Tenant\Cms_user::create($validated);
            } else {
                Cms_user::create($validated);
            }

            \Log::info('Formulario guardado en BD', [
                'email' => $validated['email'] ?? 'no-email',
                'email_destino' => $validated['email_destino'] ?? 'no-email_destino',
                'sujeto' => $validated['sujeto'] ?? 'no-sujeto'
            ]);

            // Envío de correo (solo si hay email_destino y SMTP configurado)
            $mailSent = false;
            $mailError = null;
            $shouldSendEmail = !empty($validated['email_destino']);

            if ($shouldSendEmail && $smtpConfig && $this->isValidSmtpConfig($smtpConfig)) {
                $mailFromName = trim(str_replace(['"', "'"], '', $smtpConfig->mail_from_name ?? 'Formulario Web'));
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

                try {
                    $emailDestino = $validated['email_destino'];
                    $sujeto = $validated['sujeto'] ?? '📩 Nuevo mensaje recibido desde el sitio web';

                    Mail::send('emails.contacto', ['data' => $validated], function ($message) use ($emailDestino, $sujeto, $smtpConfig) {
                        $message->to($emailDestino)->subject($sujeto);
                        $message->from(
                            $smtpConfig->mail_from_address ?? 'no-reply@sitekonecta.com',
                            $smtpConfig->mail_from_name ?? 'Formulario Web'
                        );
                    });

                    $mailSent = true;
                    \Log::info('Correo enviado a email_destino', ['email_destino' => $emailDestino]);
                } catch (\Exception $e) {
                    $mailError = $e->getMessage();
                    \Log::error('Error al enviar correo: ' . $mailError);
                }
            } else {
                $mailError = $shouldSendEmail ? 'Configuración SMTP incompleta o no disponible' : 'No se proporcionó destinatario';
            }

            $successMessage = '✅ ¡Formulario enviado correctamente!';
            if ($mailSent) {
                $successMessage .= ' y correo notificado al destinatario';
            } elseif (!$shouldSendEmail) {
                $successMessage .= ' Los datos han sido registrados correctamente';
            } else {
                $successMessage .= ' Los datos han sido registrados, pero el correo no pudo ser enviado';
            }

            return back()->with('success', $successMessage);

        } catch (\Throwable $e) {
            \Log::error('Error crítico en submitForm: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error'   => 'Error interno del servidor. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    public function testSmtpConfig()
    {
        try {
            if ($this->isTenantContext()) {
                $smtpConfig = \Sitedigitalweb\Pagina\Tenant\Cms_smtp_configs::first();
            } else {
                $smtpConfig = Cms_smtp_configs::first();
            }

            if (!$smtpConfig) {
                return response()->json(['error' => 'No hay configuración SMTP'], 404);
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtpConfig->mail_host,
                'mail.mailers.smtp.port' => $smtpConfig->mail_port,
                'mail.mailers.smtp.username' => $smtpConfig->mail_username,
                'mail.mailers.smtp.password' => $smtpConfig->mail_password,
                'mail.mailers.smtp.encryption' => $smtpConfig->mail_encryption,
            ]);

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

    public function exportCmsUsers()
    {
        try {
            return Excel::download(new CmsUsersExport, 'cms_usuarios_' . date('Y-m-d_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Error al exportar usuarios CMS: ' . $e->getMessage());
            return back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    public function showImportForm()
    {
        return view('cms_import');
    }

    public function importCmsUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ], [
            'file.required' => 'Por favor, seleccione un archivo.',
            'file.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV (.csv).',
            'file.max' => 'El archivo no debe superar los 10MB.',
        ]);

        try {
            $import = new CmsUsersImport();
            Excel::import($import, $request->file('file'));
            $stats = $import->getStats();
            $message = '✅ Importación CMS completada exitosamente! ';
            if ($stats['created'] > 0) $message .= "✅ {$stats['created']} nuevos registros creados. ";
            if ($stats['updated'] > 0) $message .= "✏️ {$stats['updated']} registros actualizados. ";
            if ($stats['skipped'] > 0) $message .= "⏭️ {$stats['skipped']} filas omitidas (sin email o email inválido).";
            return back()->with('success', trim($message));
        } catch (\Exception $e) {
            Log::error('Error en importación CMS: ' . $e->getMessage());
            return back()->with('error', '❌ Error: ' . $e->getMessage());
        }
    }

    public function downloadCmsTemplate()
    {
        try {
            return Excel::download(new CmsUsersExport, 'plantilla_cms_usuarios.xlsx');
        } catch (\Exception $e) {
            Log::error('Error al descargar plantilla CMS: ' . $e->getMessage());
            return back()->with('error', 'Error al descargar plantilla.');
        }
    }

    public function estadistica()
    {
        $ip = Input::get('ip');
        $isTenant = $this->isTenantContext();

        $userModel = $isTenant
            ? \Sitedigitalweb\Estadistica\Tenant\Cms_Ips::class
            : Cms_Ips::class;

        $user = $userModel::where('ip', $ip)->first();

        if (!$user) {
            $statsModel = $isTenant
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
    }
}