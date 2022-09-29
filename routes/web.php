<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Paypal Payment
Route::get('payment', [PaymentController::class, 'index']);
Route::post('payment/charge', [PaymentController::class, 'charge']);
Route::get('payment/success', [PaymentController::class, 'success']);
Route::get('payment/error', [PaymentController::class, 'error']);


