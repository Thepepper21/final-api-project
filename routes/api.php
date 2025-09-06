<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

// ge fix nako ang images kay sayop ang url ari
Route::prefix('images')->group(function () {
    Route::get('/', [ImageController::class, 'index']);
    Route::post('/', [ImageController::class, 'store']);
    Route::get('/{image}', [ImageController::class, 'show']);
    Route::get('/{image}/file', [ImageController::class, 'serve']);
    Route::put('/{image}', [ImageController::class, 'update']);
    Route::patch('/{image}', [ImageController::class, 'update']);
    Route::delete('/{image}', [ImageController::class, 'destroy']);
});


