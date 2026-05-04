<?php

namespace Sitedigitalweb\Pagina\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cms_departamento extends Model
{
    protected $table = 'cms_departamentos';

    protected $fillable = [
        'tenant_id',
        'departamento',
        'pais_id',
    ];

    // Retorna globales + propios del tenant
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

    public function pais()
    {
        return $this->belongsTo(Cms_pais::class, 'pais_id');
    }
}