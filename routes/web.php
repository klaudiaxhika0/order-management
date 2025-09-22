<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/logout', function () {
    return view('auth.logout');
});

// Protected routes (require authentication)
Route::middleware(['web'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    });

    // Products
    Route::get('/products', function () {
        return view('products.index');
    });
    Route::get('/products/create', function () {
        return view('products.create');
    });
    Route::get('/products/{id}', function () {
        return view('products.edit');
    });

    // Customers
    Route::get('/customers', function () {
        return view('customers.index');
    });
    Route::get('/customers/create', function () {
        return view('customers.create');
    });
    Route::get('/customers/{id}', function () {
        return view('customers.edit');
    });

    // Orders
    Route::get('/orders', function () {
        return view('orders.index');
    });
    Route::get('/orders/create', function () {
        return view('orders.create');
    });
    Route::get('/orders/{id}', function () {
        return view('orders.edit');
    });
});
