<?php 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhook\CassoWebhookController;
// URL mà bạn sẽ khai báo với Casso:
// https://your-domain.com/api/webhooks/casso
Route::post('/webhooks/casso', [CassoWebhookController::class, 'handle']);
Route::get('/test-casso-signature', [App\Http\Controllers\Webhook\CassoWebhookController::class, 'testSignature']);
