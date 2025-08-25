<?php

use App\Http\Controllers\CakeCalendarController;
use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [CakeCalendarController::class, 'index'])->name('home');
Route::get('/upload', [FileUploadController::class, 'show'])->name('upload.show');
Route::post('/upload', [FileUploadController::class, 'store'])->name('upload.store');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
