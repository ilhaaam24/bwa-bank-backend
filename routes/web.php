<?php

use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix'=>'admin'], function(){
    Route::view('/','dashboard')->name('admin.dashboard');
    Route::get('transactions', [TransactionController::class, 'index'])->name('admin.transaction.index');
});