<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = [
        'multa_id',
        'entidad',
        'monto',
        'referencia',
        'fecha_pago'
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_pago' => 'datetime',
        ];
    }

    public function multa(): BelongsTo
    {
        return $this->belongsTo(Multa::class);
    }
}