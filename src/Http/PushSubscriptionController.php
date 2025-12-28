<?php

namespace Sitedigitalweb\Pagina\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{

 private function resolveUserModel()
    
    {
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    return $website 
        ? \Sitedigitalweb\Pagina\Tenant\PushSubscription::class
        : \Sitedigitalweb\Pagina\PushSubscription::class;
    }

    public function subscribe(Request $request)
    {
        $model = $this->resolveUserModel();
        $user = auth()->user();

        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $model::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id'   => $user?->id,
                'public_key' => $request->keys['p256dh'],
                'auth_token' => $request->keys['auth'],
            ]
        );

        return response()->json([
            'message' => 'Suscripción guardada correctamente'
        ]);
    }

    public function unsubscribe(Request $request)
    {

    $model = $this->resolveUserModel();
    $request->validate([
        'endpoint' => 'required|string',
    ]);

    $model::where('endpoint', $request->endpoint)->delete();

    return response()->json([
        'message' => 'Suscripción eliminada correctamente'
    ]);
    }
}