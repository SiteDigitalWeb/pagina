<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Cms_smtp_configs extends Model
{
    use BelongsToTenant;

    protected $table = 'cms_smtp_configs';

    protected $fillable = [
        'tenant_id',
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'mailgun_domain',
        'mailgun_secret',
    ];

    protected $hidden = [
        'mail_password',
        'mailgun_secret',
    ];
} 