<?php

namespace Sitedigitalweb\Pagina\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Cms_Recaptcha extends Model
{
    use UsesTenantConnection;
    protected $table = 'cms_recaptcha'; // o el nombre real de tu tabla
 
    protected $fillable = [
        'site_key',
        'secret_key',
    ];
}




