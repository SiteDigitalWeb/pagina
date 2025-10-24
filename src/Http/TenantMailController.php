<?php

namespace Sitedigitalweb\Pagina\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sitedigitalweb\Pagina\Cms_smtp_configs;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\TenantTestMail;
use Illuminate\Support\Facades\Log;





class TenantMailController extends Controller
{
    // Mostrar formulario de configuración SMTP del tenant (edición)
    public function edit(Request $request)
    {

        // usar tenant actual (resuelto por middleware) o pasar por id si admin
        $tenant = Cms_smtp_configs::first(); // fallback
        return view('pagina::smtp.edit', compact('tenant'));
    }

    // Guardar configuración SMTP del tenant
    public function update(Request $request)
    {
        $tenant = app('tenant') ?? Cms_smtp_config::first();

        $data = $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'nullable|string',
        ]);

        // Encriptar contraseña antes de guardar
        if (!empty($data['mail_password'])) {
            $data['mail_password'] = encrypt($data['mail_password']);
        } else {
            unset($data['mail_password']); // no sobreescribir si viene vacío
        }

        $tenant->fill($data);
        $tenant->save();

        return redirect()->back()->with('success', 'Configuración SMTP guardada.');
    }



public function sendTestMail(Request $request)
{
    $tenant = Cms_smtp_configs::first();

    if (!$tenant) {
        return redirect()->route('tenant.mail.index')
            ->with('error', '❌ No se encontró configuración SMTP en la base de datos.');
    }

    // Validar configuración mínima
    $requiredFields = [
        'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_from_address', 'mail_from_name'
    ];
    foreach ($requiredFields as $field) {
        if (empty($tenant->$field)) {
            return redirect()->route('tenant.mail.index')
                ->with('error', "⚠️ Falta el campo obligatorio en configuración: {$field}");
        }
    }

    $to = $request->input('email', 'darioma07@hotmail.com');

    try {
        // Configuración dinámica del mailer
        config([
    'mail.mailers.smtp.transport' => 'smtp',
    'mail.mailers.smtp.host' => $tenant->mail_host,
    'mail.mailers.smtp.port' => $tenant->mail_port,
    'mail.mailers.smtp.username' => $tenant->mail_username,
    'mail.mailers.smtp.password' => $tenant->mail_password,
    'mail.mailers.smtp.encryption' => $tenant->mail_encryption ?? 'tls',
    'mail.from.address' => $tenant->mail_from_address,
    'mail.from.name' => $tenant->mail_from_name ?? 'Tenant',
    'mail.mailers.smtp.stream' => [
        'ssl' => [
            'allow_self_signed' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ],
]);

        // Intentar envío
        Mail::to($to)->send(new TenantTestMail(
            $tenant->mail_from_name,
            "📧 Este es un correo de prueba enviado desde el tenant: {$tenant->mail_from_name}"
        ));

        return redirect()->route('tenant.mail.edit')
            ->with('success', "✅ Correo de prueba enviado correctamente a {$to}");
    } catch (\Throwable $e) {
        // Log completo en storage/logs/laravel.log
        Log::error('Error al enviar correo de prueba', [
            'tenant' => $tenant->id ?? null,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        // Mensaje detallado para la vista
        $message = "❌ Error al enviar correo: " . $e->getMessage();

        if (config('app.debug')) {
            $message .= " (Archivo: " . basename($e->getFile()) . " Línea: " . $e->getLine() . ")";
        }

        return redirect()->route('tenant.mail.edit')
            ->with('error', $message);
    }
}


}