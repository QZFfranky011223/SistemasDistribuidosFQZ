<?php

use App\Http\Controllers\MultaController;
use Illuminate\Support\Facades\Route;

// Recurso REST principal solicitado en el examen: "El recurso se llama multa"
Route::get('/multa', [MultaController::class, 'index']);
Route::post('/multa', [MultaController::class, 'store']);
Route::get('/multa/{idOrCodigo}', [MultaController::class, 'show']);
Route::put('/multa/{idOrCodigo}', [MultaController::class, 'pagar']);
Route::put('/multa/{idOrCodigo}/pagar', [MultaController::class, 'pagar']);

// Alias en plural por comodidad (/api/multas)
Route::get('/multas', [MultaController::class, 'index']);
Route::post('/multas', [MultaController::class, 'store']);
Route::get('/multas/{idOrCodigo}', [MultaController::class, 'show']);
Route::put('/multas/{idOrCodigo}/pagar', [MultaController::class, 'pagar']);
