<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::post('/make_appointment', [AppointmentController::class, 'store_appointment'])->name('make_appointment');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
