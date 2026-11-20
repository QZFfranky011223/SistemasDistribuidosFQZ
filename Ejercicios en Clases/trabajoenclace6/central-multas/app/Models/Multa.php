<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Multa extends Model
{
    protected $fillable = [
        'codigo',
        'ci',
        'nombre_conductor',
        'tipo_infraccion',
        'descripcion',
        'lugar',
        'fecha_infraccion',
        'monto',
        'estado',
        'policia_id'
    ];

    protected function casts(): array
    {
        return [
            'fecha_infraccion' => 'datetime',
            'monto' => 'decimal:2',
        ];
    }

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class);
    }
}