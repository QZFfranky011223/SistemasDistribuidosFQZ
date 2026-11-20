<?php

use App\Http\Controllers\MultaController;
use Illuminate\Support\Facades\Route;

// Vista principal del Cliente Web en http://localhost:8000
Route::get('/', function () {
    $clienteHtml = base_path('../cliente-web/index.html');
    if (file_exists($clienteHtml)) {
        return response(file_get_contents($clienteHtml), 200)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
    return response()->json([
        'sistema' => 'Central de Registro de Multas de Tránsito - Servicio REST Laravel',
        'recurso_rest' => '/api/multa',
    ]);
});

// También disponible en /multa sin prefijo /api por si se invoca directamente
Route::get('/multa', [MultaController::class, 'index']);
Route::post('/multa', [MultaController::class, 'store']);
Route::get('/multa/{idOrCodigo}', [MultaController::class, 'show']);
Route::put('/multa/{idOrCodigo}', [MultaController::class, 'pagar']);
Route::put('/multa/{idOrCodigo}/pagar', [MultaController::class, 'pagar']);
