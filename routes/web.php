<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix'=>'admin'], function(){
    Route::view('login','login')->name('admin.login');
    Route::post('login',[AdminAuthController::class, 'login'])->name('admin.login.login');
    Route::view('/','dashboard')->name('admin.dashboard');
    Route::get('transactions', [TransactionController::class, 'index'])->name('admin.transaction.index');
});