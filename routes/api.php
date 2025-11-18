<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DependencyController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PeripheralChangeHistoryController;

// 🟢 Rutas públicas (auth)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Ruta de prueba para verificar configuración de storage
Route::get('test-storage', function () {
    return response()->json([
        'message' => 'Storage configuration test',
        'app_url' => config('app.url'),
        'storage_link_exists' => is_link(public_path('storage')),
        'sample_urls' => [
            'format_example' => url('storage/maintenances/formats/example.pdf'),
            'image_example' => url('storage/maintenances/images/example.jpg'),
        ],
        'instructions' => 'Use physical_format_url and image_url fields from API responses'
    ]);
});

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

    // Mantenimientos
    Route::apiResource('maintenances', MaintenanceController::class);
    Route::post('maintenances/{id}/images', [MaintenanceController::class, 'addImage']);
    Route::delete('maintenances/{maintenanceId}/images/{imageId}', [MaintenanceController::class, 'deleteImage']);
    Route::get('upcoming-maintenances', [MaintenanceController::class, 'upcomingMaintenances']);

    // Historial de cambios de periféricos
    Route::apiResource('peripheral-changes', PeripheralChangeHistoryController::class);
    Route::get('devices/{deviceId}/peripheral-changes', [PeripheralChangeHistoryController::class, 'getByDevice']);
    Route::get('peripheral-changes-stats', [PeripheralChangeHistoryController::class, 'stats']);
});
