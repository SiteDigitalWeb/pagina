<?php

namespace Sitedigitalweb\Pagina\Tenant;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;


class Cms_SavedComponent extends Model
{
     use BelongsToTenant;
    protected $table = 'cms_components'; // nombre de la tabla en la BD
    protected $fillable = ['tenant_id','label', 'content', 'category'];
}