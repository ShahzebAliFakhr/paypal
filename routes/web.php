<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\AppleController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');


// Login with Google
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Login with Apple
Route::get('auth/apple', [AppleController::class, 'redirectToApple']);
Route::get('auth/apple/callback', [AppleController::class, 'handleAppleCallback']);

// Paypal Payment
Route::get('payment', [PaymentController::class, 'index']);
Route::post('payment/charge', [PaymentController::class, 'charge']);
Route::get('payment/success', [PaymentController::class, 'success']);
Route::get('payment/error', [PaymentController::class, 'error']);

// Google Drive Upload
Route::get('google', [GoogleDriveController::class, 'index']);
Route::get('google/login', [GoogleDriveController::class, 'login']);
Route::post('google/upload', [GoogleDriveController::class, 'upload']);

// Backup
Route::get('backup/run', function(){
    \Artisan::call('backup:run --only-db --disable-notifications');
    dd('Backup Successfull.');
});

require __DIR__.'/auth.php';
