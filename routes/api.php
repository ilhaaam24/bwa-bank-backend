<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataPlanController;
use App\Http\Controllers\Api\OperatorCardController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\TopUpController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\TransferHistoryController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Middleware\JwtMiddleware;
use App\Models\OperatorCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/tes', function () {
  return response()->json(['message'=>'success']);
})->middleware(JwtMiddleware::class);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::post('/webhook', [WebhookController::class, 'update']);

Route::group(['middleware' => 'jwt.auth'], function($router){
  Route::post('top_ups', [TopUpController::class, 'store']);
  Route::post('transfers', [TransferController::class, 'store']);
  Route::post('data_plans', [DataPlanController::class, 'store']);
  Route::get('operator_cards', [OperatorCardController::class, 'index']);
  Route::get('payment_methods', [PaymentMethodController::class, 'index']);
  Route::get('transfer_histories', [TransferHistoryController::class, 'index']);
});