<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// web.php
Route::get('/admin/{any?}', function () {
    return file_get_contents(public_path('admin/index.html'));
})->where('any', '.*');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('/appointment', [AppointmentController::class, 'index'])->name('appointment.index');
    Route::get('/appointment/create', [AppointmentController::class, 'create'])->name('appointment.create');
    Route::post('/appointment/store', [AppointmentController::class, 'store'])->name('appointment.store');

});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
