<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\DashboardController;

Route::get('/', DashboardController::class)->name('dashboard');
Route::resource('medicamentos', MedicamentoController::class);
