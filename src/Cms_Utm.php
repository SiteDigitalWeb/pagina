<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Cms_utm extends Model
{
    use BelongsToTenant;

    protected $table = 'cms_utms';

    protected $fillable = [
        'tenant_id',
        'campaign_name',
        'source',
        'medium',
        'term',
        'content',
        'final_url',
    ];
}

