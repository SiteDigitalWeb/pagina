<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;

class Cms_smtp_configs extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
    ];

    // Accessor para devolver la contraseña desencriptada
    public function getDecryptedMailPasswordAttribute()
    {
        if (!$this->mail_password) return null;
        try {
            return decrypt($this->mail_password);
        } catch (\Exception $e) {
            return null;
        }
    }
}