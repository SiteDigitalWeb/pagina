<?php

namespace Sitedigitalweb\Pagina\Tenant;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Cms_Recaptcha extends Model
{
    use BelongsToTenant;

    protected $table = 'cms_recaptcha';

    protected $fillable = [
        'tenant_id',
        'site_key',
        'secret_key',
    ];
}
