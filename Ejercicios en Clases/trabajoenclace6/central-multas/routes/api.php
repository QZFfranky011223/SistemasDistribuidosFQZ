<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MultaController;

// API CENTRAL DE REGISTRO DE MULTAS

Route::prefix('multas')->group(function () {

    // Registrar multa
    Route::post('/', [MultaController::class, 'store']);

    // Consultar y filtrar multas
    Route::get('/', [MultaController::class, 'index']);

    // Consultar multa por código
    Route::get('/{codigo}', [MultaController::class, 'show']);

    // Registrar pago
    Route::post('/{codigo}/pagar', [MultaController::class, 'pagar']);

    // Anular multa
    Route::put('/{codigo}/anular', [MultaController::class, 'anular']);

});