<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class MedicamentoRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'nombre_comercial' => 'required|string|max:80',
            'principio_activo' => 'required|string',
            'presentacion' => 'required|in:Tabletas,Jarabe,Inyectable,Cápsulas',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'requiere_receta' => 'required|boolean',
            'laboratorio_id' => 'required|exists:laboratorios,id',
        ];
    }
    public function messages(): array {
        return [
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'nombre_comercial.max' => 'El nombre comercial no puede superar 80 caracteres.',
            'precio.min' => 'El precio debe ser mayor o igual a 0.',
            'stock.min' => 'El stock debe ser mayor o igual a 0.',
            'laboratorio_id.exists' => 'El laboratorio seleccionado no existe.',
        ];
    }
}
