<?php

use App\Http\Controllers\AcUnitController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest (unauthenticated) routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    | Shared read access (admin + owner)
    */
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('technicians', [TechnicianController::class, 'index'])->name('technicians.index');
    });

    /*
    | Booking detail + status update (all roles; controller guards ownership)
    */
    Route::middleware('role:admin,owner,technician')->group(function () {
        Route::get('bookings/{booking}', [BookingController::class, 'show'])
            ->whereNumber('booking')->name('bookings.show');
    });

    Route::middleware('role:admin,technician')->group(function () {
        Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus'])
            ->name('bookings.status');
    });

    /*
    | Owner-only reports
    */
    Route::middleware('role:owner')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    /*
    | Admin operational management
    */
    Route::middleware('role:admin')->group(function () {
        // Bookings
        Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::post('bookings/{booking}/assign', [BookingController::class, 'assign'])->name('bookings.assign');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::get('customers/{customer}/units-json', [BookingController::class, 'unitsForCustomer'])
            ->name('bookings.units');

        // Payments & reviews
        Route::post('bookings/{booking}/payment', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('bookings/{booking}/review', [ReviewController::class, 'store'])->name('reviews.store');

        // Customers
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // AC units (nested under customer for create/store)
        Route::get('customers/{customer}/units/create', [AcUnitController::class, 'create'])->name('ac-units.create');
        Route::post('customers/{customer}/units', [AcUnitController::class, 'store'])->name('ac-units.store');
        Route::get('units/{acUnit}/edit', [AcUnitController::class, 'edit'])->name('ac-units.edit');
        Route::put('units/{acUnit}', [AcUnitController::class, 'update'])->name('ac-units.update');
        Route::delete('units/{acUnit}', [AcUnitController::class, 'destroy'])->name('ac-units.destroy');

        // Services
        Route::get('services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        // Users
        Route::resource('users', UserController::class)->except(['show']);

        // Technician status
        Route::patch('technicians/{technician}/status', [TechnicianController::class, 'updateStatus'])
            ->name('technicians.status');
    });

    /*
    | Customer detail (admin + owner read). Placed after literal admin routes
    | to avoid clashing with customers/create; numeric constraint enforced.
    */
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('customers/{customer}', [CustomerController::class, 'show'])
            ->whereNumber('customer')->name('customers.show');
    });
});
