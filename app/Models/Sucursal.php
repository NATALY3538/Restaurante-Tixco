<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    use SoftDeletes;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion_calle',
        'colonia_ciudad',
        'codigo_postal',
        'telefono_contacto',
        'email_contacto',
        'rfc_identificacion_fiscal',
        'horario_apertura',
        'horario_cierre',
        'dias_operacion',
        'is_matriz',
        'is_active',
    ];

    protected $casts = [
        'dias_operacion' => 'array',
        'is_matriz' => 'boolean',
        'is_active' => 'boolean',
    ];
}
