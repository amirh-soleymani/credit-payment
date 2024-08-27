<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CreditRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::post('register', [AuthenticationController::class, 'register']);
Route::post('login', [AuthenticationController::class, 'login'])->name('login');
Route::post('logout', [AuthenticationController::class, 'logout'])
    ->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
   Route::post('/sendRequest', [CreditRequestController::class, 'sendRequest']);
   Route::get('/sellerCreditRequestList', [CreditRequestController::class, 'sellerCreditRequestList']);
   Route::post('/checkClientCredit', [CreditRequestController::class, 'checkClientCredit']);
   Route::get('/clientCreditRequestList', [CreditRequestController::class, 'clientCreditRequestList']);
   Route::post('/sellerAcceptCreditRequest', [CreditRequestController::class, 'sellerAcceptCreditRequest']);
   Route::post('/sellerDenyCreditRequest', [CreditRequestController::class, 'sellerDenyCreditRequest']);

});
