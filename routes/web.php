<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// akong ge update diri ambrad
Route::get('gallery', function () {
    return Inertia::render('ImageGallery');
})->middleware(['auth', 'verified'])->name('gallery');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
