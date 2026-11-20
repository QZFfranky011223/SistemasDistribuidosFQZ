<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model {
    use HasFactory;
    protected $fillable = ['nombre_comercial','principio_activo','presentacion','precio','stock','requiere_receta','laboratorio_id'];
    public function laboratorio() {
        return $this->belongsTo(Laboratorio::class);
    }
}
