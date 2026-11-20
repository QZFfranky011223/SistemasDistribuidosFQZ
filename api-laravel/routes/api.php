<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CessaFacturaController;
use App\Http\Controllers\Api\ElapasFacturaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('clients', ClientController::class);

// --- Rutas del Servicio CESSA REST (Recurso Facturas) ---
Route::prefix('cessa')->group(function () {
    Route::get('/facturas', [CessaFacturaController::class, 'index']);
    Route::post('/facturas/pagar', [CessaFacturaController::class, 'pagar']);
});

// --- Rutas del Servicio ELAPAS REST (Recurso Facturas) ---
Route::prefix('elapas')->group(function () {
    Route::get('/facturas', [ElapasFacturaController::class, 'index']);
    Route::post('/facturas/pagar', [ElapasFacturaController::class, 'pagar']);
});
