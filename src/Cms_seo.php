<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Cms_seo extends Model
{
    use BelongsToTenant;

    protected $table = 'cms_seo';

    protected $fillable = [
        'tenant_id',
        'robots',
        'og_type',
        'og_image',
        'og_url',
        'og_title',
        'og_description',
        'og_name',
        'twitter_card',
        'twitter_site',
        'twitter_creator',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'canonical',
        'idioma',
        'ico',
        'icoapple',
        'analitica',
        'ads',
    ];
}
