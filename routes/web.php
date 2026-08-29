<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\FormBuilderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer authentication
|--------------------------------------------------------------------------
| Accounts are issued by an admin, so there is no public sign up.
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [CustomerAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');
});

Route::post('logout', [CustomerAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Customer area (requires a signed in account)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/', [OrderController::class, 'create'])->name('order.create');
    Route::post('/order', [OrderController::class, 'store'])->name('order.store');
    Route::get('/my-orders', [OrderController::class, 'history'])->name('order.history');
    Route::get('/products/{product}/prices', [OrderController::class, 'prices'])->name('products.prices');

    // The customer's own orders, with filters and voice notes.
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('order.list');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('order.show');
    Route::post('/orders/{order}/voice-note', [CustomerOrderController::class, 'storeVoiceNote'])->name('order.voice-note.store');
    Route::delete('/orders/{order}/voice-note', [CustomerOrderController::class, 'destroyVoiceNote'])->name('order.voice-note.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');
    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class)->except('show');
        Route::resource('users', UserController::class)->except('show');

        Route::get('form-builder', [FormBuilderController::class, 'index'])->name('form-builder');
        Route::get('form-builder/preview', [FormBuilderController::class, 'preview'])->name('form-builder.preview');
        Route::post('form-builder', [FormBuilderController::class, 'save'])->name('form-builder.save');

        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    });
});
