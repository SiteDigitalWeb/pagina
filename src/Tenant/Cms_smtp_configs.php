<?php

namespace Sitedigitalweb\Pagina\Tenant;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cms_smtp_configs extends Model
{

    protected $fillable = [
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
}