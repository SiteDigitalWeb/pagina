<?php
namespace Sitedigitalweb\Pagina\Tenant;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Cms_Template extends Model
{
    use BelongsToTenant;

    protected $table = 'cms_templates';

    protected $fillable = [
        'tenant_id',
        'template',
        'description',
        'image',
        'url',
    ];
}