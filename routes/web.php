<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\UserController;

Route::get('/', [HomeController::class, 'index'])->name('welcome');
Route::get('/how-it-works', [HomeController::class, 'howItWorks'])->name('how-it-works');
Route::get('/about-us', [HomeController::class, 'about'])->name('about');
Route::get('/contact-us', [HomeController::class, 'contact'])->name('contact');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'redirectToDashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/pharmacies/{pharmacy}/approve-subscription', [AdminController::class, 'approveSubscription'])
        ->name('pharmacies.approve-subscription');
    Route::patch('/pharmacies/{pharmacy}/status', [AdminController::class, 'updatePharmacyStatus'])
        ->name('pharmacies.update-status');
    Route::delete('/pharmacies/{pharmacy}', [AdminController::class, 'destroyPharmacy'])
        ->name('pharmacies.destroy');
    Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])
        ->name('users.update-status');
});

Route::prefix('pharmacy')->name('pharmacy.')->middleware(['auth', 'role:pharmacy'])->group(function () {
    Route::get('/subscription-required', [PharmacyController::class, 'subscriptionRequired'])->name('subscription-required');

    Route::middleware('pharmacy.subscription')->group(function () {
        Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');
        Route::post('/medicines', [PharmacyController::class, 'storeMedicine'])->name('medicines.store');
        Route::get('/adverts', [PharmacyController::class, 'adverts'])->name('adverts.index');
        Route::post('/adverts', [PharmacyController::class, 'storeAdvert'])->name('adverts.store');
    });
});

Route::prefix('user')->name('user.')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::post('/orders', [UserController::class, 'storeOrder'])->name('orders.store');
    Route::post('/medicines/{medicine}/reserve', [UserController::class, 'reserve'])->name('medicines.reserve');
});
