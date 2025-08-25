<?php

use App\Http\Controllers\CakeCalendarController;
use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CakeCalendarController::class, 'index'])->name('home');
Route::get('/upload', [FileUploadController::class, 'show'])->name('upload.show');
Route::post('/upload', [FileUploadController::class, 'store'])->name('upload.store');
