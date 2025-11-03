<?php

namespace Sitedigitalweb\Pagina\Http;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Sitedigitalweb\Pagina\Cms_smtp_configs;
use Illuminate\Support\Facades\Mail;
use Exception;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class SmtpConfigController extends Controller
{
    /**
     * Mostrar formulario de configuración SMTP
     */
     public function index()
{
    // Detectar si estamos en un tenant
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    // Si estamos en tenant, obtener configuración del tenant actual
    if ($website) {
        $config = \Sitedigitalweb\Pagina\Tenant\Cms_smtp_configs::first();
    } else {
        // En la base central, obtener configuración global
        $config = Cms_smtp_configs::first();
    }

    return view('pagina::smtp.index', compact('config'));
}


    /**
     * Guardar configuración SMTP con validación mejorada
     */
    public function store(Request $request)
    {
        try {
            // Validación más detallada
            $validator = Validator::make($request->all(), [
                'mail_host' => 'required|string|max:255',
                'mail_port' => 'required|string|max:10',
                'mail_encryption' => 'nullable|string|in:tls,ssl',
                'mail_username' => 'required|string|email|max:255',
                'mail_password' => 'required|string|min:6',
                'mail_from_address' => 'required|email|max:255',
                'mail_from_name' => 'required|string|max:255',
                'mailgun_domain' => 'required|string|max:255',
                'mailgun_secret' => 'nullable|string|max:255',
            ], [
                'mail_host.required' => 'El host de correo es obligatorio',
                'mail_port.required' => 'El puerto de correo es obligatorio',
                'mail_username.required' => 'El nombre de usuario es obligatorio',
                'mail_username.email' => 'El nombre de usuario debe ser un email válido',
                'mail_password.required' => 'La contraseña es obligatoria',
                'mail_password.min' => 'La contraseña debe tener al menos 6 caracteres',
                'mail_from_address.required' => 'La dirección de remitente es obligatoria',
                'mail_from_address.email' => 'La dirección de remitente debe ser un email válido',
                'mail_from_name.required' => 'El nombre del remitente es obligatorio',
                'mailgun_domain.required' => 'El dominio de Mailgun es obligatorio',
                'mailgun_secret.required' => 'El secreto de Mailgun es obligatorio',
                'mail_encryption.in' => 'El cifrado debe ser tls o ssl',
            ]);

            // Verificar si la validación falla
            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Detectar si estamos en un tenant
            $website = app(\Hyn\Tenancy\Environment::class)->website();

            // Si estamos en tenant, usar el modelo del tenant
            if ($website) {
            $config = \Sitedigitalweb\Pagina\Tenant\Cms_smtp_configs::updateOrCreate(
                ['id' => 1], 
            $validator->validated()
            );
            } else {
            // En la base central, usar el modelo global
            $config = Cms_smtp_configs::updateOrCreate(
            ['id' => 1], 
            $validator->validated()
            );
            }

            if (!$config) {
                return back()
                    ->withErrors(['general' => 'No se pudo guardar la configuración en la base de datos'])
                    ->withInput();
            }

            return back()->with('success', '✅ Configuración guardada correctamente');

        } catch (Exception $e) {
            return back()
                ->withErrors(['smtp_connection' => 'Error al guardar: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Método alternativo para probar conexión SMTP (más simple)
     */
    private function testSmtpConnection($config)
    {
        try {
            // Enfoque simple: intentar crear un transporte SMTP
            $transport = new \Swift_SmtpTransport(
                $config['mail_host'],
                $config['mail_port'],
                $config['mail_encryption'] ?? 'tls'
            );
            
            $transport->setUsername($config['mail_username']);
            $transport->setPassword($config['mail_password']);
            
            // Timeout más corto para pruebas
            $transport->setTimeout(10);
            
            // Crear el mailer
            $mailer = new \Swift_Mailer($transport);
            
            // Probar la conexión
            $mailer->getTransport()->start();
            
            return true;

        } catch (Exception $e) {
            throw new Exception('Error de conexión SMTP: ' . $e->getMessage());
        }
    }

    /**
     * Enviar correo de prueba (versión simplificada)
     */
    public function testEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email_test' => 'required|email|max:255'
            ], [
                'email_test.required' => 'El email de prueba es obligatorio',
                'email_test.email' => 'Debe ingresar un email válido',
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('test_error', '❌ Error en el email de prueba');
            }

            $config = Cms_smtp_configs::find(1);
            
            if (!$config) {
                return back()
                    ->with('test_error', '❌ No hay configuración SMTP guardada. Guarda la configuración primero.');
            }

            // Configurar temporalmente (esto funciona con Laravel)
            config([
                'mail.mailers.smtp.host' => $config->mail_host,
                'mail.mailers.smtp.port' => $config->mail_port,
                'mail.mailers.smtp.encryption' => $config->mail_encryption ?? 'tls',
                'mail.mailers.smtp.username' => $config->mail_username,
                'mail.mailers.smtp.password' => $config->mail_password,
                'mail.from.address' => $config->mail_from_address,
                'mail.from.name' => $config->mail_from_name,
            ]);

            // Enviar correo de prueba simple
            Mail::raw('Este es un correo de prueba para verificar la configuración SMTP.', function ($message) use ($request, $config) {
                $message->to($request->email_test)
                        ->subject('Prueba de configuración SMTP - ' . config('app.name', 'Sistema'))
                        ->from($config->mail_from_address, $config->mail_from_name);
            });

            return back()->with('test_success', '✅ Correo de prueba enviado correctamente a: ' . $request->email_test);

        } catch (Exception $e) {
            return back()
                ->withErrors(['email_test' => 'Error al enviar: ' . $e->getMessage()])
                ->withInput()
                ->with('test_error', '❌ Error al enviar correo de prueba');
        }
    }

    /**
     * Método alternativo si el anterior falla
     */
    public function testEmailSimple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email_test' => 'required|email|max:255'
            ]);

            if ($validator->fails()) {
                return back()->with('test_error', 'Email de prueba inválido');
            }

            $config = Cms_smtp_configs::find(1);
            
            if (!$config) {
                return back()->with('test_error', 'No hay configuración SMTP guardada');
            }

            // Enfoque más simple usando mail nativo de PHP (solo para pruebas)
            $to = $request->email_test;
            $subject = 'Prueba SMTP - ' . date('Y-m-d H:i:s');
            $message = 'Esta es una prueba de la configuración SMTP. Si recibes este correo, la configuración es correcta.';
            $headers = "From: " . $config->mail_from_address . "\r\n" .
                       "Reply-To: " . $config->mail_from_address . "\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            if (@mail($to, $subject, $message, $headers)) {
                return back()->with('test_success', '✅ Correo de prueba enviado (método simple)');
            } else {
                return back()->with('test_error', '❌ Error al enviar correo (método simple)');
            }

        } catch (Exception $e) {
            return back()->with('test_error', '❌ Error: ' . $e->getMessage());
        }
    }



    /**
 * Enviar correo de prueba - Versión con nombre sendTestMail
 */
public function sendTestMail(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'email_test' => 'required|email|max:255'
        ], [
            'email_test.required' => 'El email de prueba es obligatorio',
            'email_test.email' => 'Debe ingresar un email válido',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('test_error', '❌ Error en el email de prueba');
        }

        // Detectar si estamos en un tenant
        $website = app(\Hyn\Tenancy\Environment::class)->website();

        // Obtener configuración según el contexto
        if ($website) {
            $config = \Sitedigitalweb\Pagina\Tenant\Cms_smtp_configs::first();
        } else {
            $config = Cms_smtp_configs::first();
        }
        
        if (!$config) {
            return redirect()
                ->back()
                ->with('test_error', '❌ No hay configuración SMTP guardada. Guarda la configuración primero.');
        }

        // Configurar temporalmente
        config([
            'mail.mailers.smtp.host' => $config->mail_host,
            'mail.mailers.smtp.port' => $config->mail_port,
            'mail.mailers.smtp.encryption' => $config->mail_encryption ?? 'tls',
            'mail.mailers.smtp.username' => $config->mail_username,
            'mail.mailers.smtp.password' => $config->mail_password,
            'mail.from.address' => $config->mail_from_address,
            'mail.from.name' => $config->mail_from_name,
            'services.mailgun.domain' => $config->mailgun_domain,
            'services.mailgun.secret' => $config->mailgun_secret,
        ]);

        // Enviar correo de prueba con HTML más elaborado
        Mail::send('smtp.test_email', [], function ($message) use ($request, $config) {
            $message->to($request->email_test)
                    ->subject('✅ Prueba de Configuración SMTP - ' . config('app.name', 'Sistema'))
                    ->from($config->mail_from_address, $config->mail_from_name);
        });

        return redirect()
            ->back()
            ->with('test_success', '✅ Correo de prueba enviado correctamente a: ' . $request->email_test);

    } catch (Exception $e) {
        return redirect()
            ->back()
            ->withErrors(['email_test' => 'Error al enviar: ' . $e->getMessage()])
            ->withInput()
            ->with('test_error', '❌ Error al enviar correo de prueba: ' . $e->getMessage());
    }
}

}

