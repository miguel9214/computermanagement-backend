<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DependencyController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ScannerController;

// 🟢 Rutas públicas (auth)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// 🔒 Rutas protegidas por JWT
Route::middleware(['auth:api'])->group(function () {

    // Usuario autenticado
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Recursos protegidos
    Route::apiResource('dependencies', DependencyController::class);
    Route::apiResource('devices', DeviceController::class);
    Route::apiResource('printers', PrinterController::class);
    Route::apiResource('scanners', ScannerController::class);
});
