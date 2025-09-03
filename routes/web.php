<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\WhatsappWebhookController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// web.php
Route::get('/admin/{any?}', function () {
    return file_get_contents(public_path('admin/index.html'));
})->where('any', '.*');

Route::get('/privacy_policy', [PageController::class, 'privacy_policy'])->name('privacy_policy');
Route::get('/terms_condition', [PageController::class, 'terms_condition'])->name('terms_condition');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointment.index');
    Route::get('/appointment/create', [AppointmentController::class, 'create'])->name('appointment.create');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointment.update');
    Route::delete('/appointment/delete/{appointment}', [AppointmentController::class, 'destroy'])->name('appointment.delete');

    Route::get('/clients', [ClientController::class, 'index'])->name('client.index');
    Route::get('/client/create', [ClientController::class, 'create'])->name('client.create');
    Route::put('/client/{client}', [ClientController::class, 'update'])->name('client.update');

    Route::put('/whatsapp/api', [WhatsappController::class, 'index'])->name('whatsapp.index');
    Route::get('/whatsapp/send', [WhatsappController::class, 'send']);

    Route::get('/webhook/whatsapp',  [WhatsappWebhookController::class, 'verify']);  // GET verify
    Route::post('/webhook/whatsapp', [WhatsappWebhookController::class, 'handle']);
});




require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
