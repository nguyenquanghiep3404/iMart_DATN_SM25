<?php 
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Webhook\CassoWebhookController;
// use App\Http\Controllers\Users\HomeController;
// // URL mà bạn sẽ khai báo với Casso:
// // https://your-domain.com/api/webhooks/casso
// Route::post('/webhooks/casso', [CassoWebhookController::class, 'handle']);
// Route::get('/test-casso-signature', [App\Http\Controllers\Webhook\CassoWebhookController::class, 'testSignature']);
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Http\Controllers\Api\ShipmentController;

Route::post('/bot/webhook', function () {
    Telegram::commandsHandler(true); // Tự động xử lý các lệnh đến
    return 'ok';
});

// Shipment API routes
Route::prefix('shipments')->group(function () {
    Route::post('/calculate', [ShipmentController::class, 'calculateShipments']);
    Route::post('/calculate-pickup', [ShipmentController::class, 'calculatePickupShipments']);
    Route::post('/shipping-fee', [ShipmentController::class, 'calculateShippingFee']);
});
