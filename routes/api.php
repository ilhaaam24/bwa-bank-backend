<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TopUpController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Middleware\JwtMiddleware;
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
});