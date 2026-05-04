<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cms_pais extends Model
{
    protected $table = 'cms_paises';

    protected $fillable = [
        'tenant_id',
        'pais',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant_or_global', function (Builder $builder) {
            if (tenancy()->initialized) {
                $builder->where(function ($q) {
                    $q->where('tenant_id', tenant('id'))
                      ->orWhereNull('tenant_id');
                });
            }
        });
    }
}