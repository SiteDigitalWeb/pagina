<?php

namespace Sitedigitalweb\Pagina;
use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class PushNotificationLog extends Model
{
    use UsesTenantConnection;
    protected $fillable = [
        'push_notification_id',
        'endpoint',
        'success',
        'error'
    ];

    public function notification()
    {
        return $this->belongsTo(PushNotification::class);
    }
}

