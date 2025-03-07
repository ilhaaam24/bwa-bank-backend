<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Route::prefix('api')->group(function () {
//     Route::post('/register', [AuthController::class, 'register']);
// });