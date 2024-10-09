<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::get('/payments/approval', [PaymentController::class, 'approval'])->name('payments.approval');
    Route::get('/payments/cancelled', [PaymentController::class, 'cancelled'])->name('payments.cancelled');
    Route::post('/payments/pay', [PaymentController::class, 'pay'])->name('payments.pay');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('subscribe')
    ->name('subscribe.')
    ->group(function () {
        Route::get('/', [SubscriptionController::class, 'show'])->name('show');
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::get('/approval', [SubscriptionController::class, 'approval'])->name('approval');
        Route::get('/cancelled', [SubscriptionController::class, 'cancelled'])->name('cancelled');
    });

require __DIR__.'/auth.php';
