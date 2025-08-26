<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ShipmentController;

// Shipment API routes
Route::prefix('shipments')->group(function () {
    Route::post('/calculate', [ShipmentController::class, 'calculateShipments']);
    Route::post('/calculate-pickup', [ShipmentController::class, 'calculatePickupShipments']);
    Route::post('/shipping-fee', [ShipmentController::class, 'calculateShippingFee']);
});
