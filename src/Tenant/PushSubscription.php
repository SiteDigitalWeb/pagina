<?php

namespace Sitedigitalweb\Pagina\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{

    use UsesTenantConnection;
    protected $fillable = [
        'user_id',
        'endpoint',
        'public_key',
        'auth_token',
    ];

    public function user()
    {
    return $this->belongsTo(User::class);
    }
}
