<?php

use App\Http\Controllers\Api\BebidaController;
use App\Http\Controllers\Api\CanchaController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\ReservaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::controller(BebidaController::class)->prefix('bebidas')->group(function(){
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::post('/{id}', 'update');
    Route::put('/{id}', 'put');
    Route::get('/{id}', 'show');
    Route::delete('/{id}', 'destroy');

    Route::get('/from_bebida/{bebidaId}', 'indexFromBebida');
});

Route::controller(CanchaController::class)->prefix('canchas')->group(function(){
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::post('/{id}', 'update');
    Route::put('/{id}', 'put');
    Route::get('/{id}', 'show');
    Route::delete('/{id}', 'destroy');

    Route::get('/from_cancha/{canchaId}', 'indexFromCancha');
});

Route::controller(FacturaController::class)->prefix('facturas')->group(function(){
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::post('/{id}', 'update');
    Route::put('/{id}', 'put');
    Route::get('/{id}', 'show');
    Route::delete('/{id}', 'destroy');

    Route::get('/from_factura/{facturaId}', 'indexFromFactura');
});

Route::controller(PagoController::class)->prefix('pagos')->group(function(){
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::post('/{id}', 'update');
    Route::put('/{id}', 'put');
    Route::get('/{id}', 'show');
    Route::delete('/{id}', 'destroy');

    Route::get('/from_pago/{pagoId}', 'indexFromPago');
});

Route::controller(ReservaController::class)->prefix('reservas')->group(function(){
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::post('/{id}', 'update');
    Route::put('/{id}', 'put');
    Route::get('/{id}', 'show');
    Route::delete('/{id}', 'destroy');

    Route::get('/from_reserva/{reservaId}', 'indexFromReserva');
});
