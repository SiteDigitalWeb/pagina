<?php

namespace Sitedigitalweb\Pagina\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;


class Cms_seo extends Model
{
	use UsesTenantConnection;
	protected $table = 'cms_seo';
	public $timestamps = true;

	protected $fillable = [
    'idioma', 'canonical', 'robots', 'og_type', 'og_image', 'og_url',
    'og_title', 'og_name', 'og_description', 'twitter_card', 'twitter_site',
    'twitter_creator', 'twitter_title', 'twitter_description', 'twitter_image',
    'analitica', 'ads', 'ico', 'icoapple'
];
}