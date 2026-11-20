<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multa extends Model
{
    use HasFactory;

    protected $table = 'multas';

    protected $fillable = [
        'codigo_unico',
        'ci_conductor',
        'nombre_conductor',
        'placa',
        'fecha',
        'tipo_infraccion',
        'lugar',
        'monto',
        'estado',
        'fecha_pago',
        'entidad_pago',
    ];

    protected $casts = [
        'monto' => 'float',
    ];
}
