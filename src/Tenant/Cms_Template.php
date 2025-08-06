<?php

namespace Sitedigitalweb\Pagina\Tenant;


use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cms_Template extends Model
{

    use UsesTenantConnection;
    protected $table = 'cms_templates';
}
