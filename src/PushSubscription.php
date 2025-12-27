<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
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