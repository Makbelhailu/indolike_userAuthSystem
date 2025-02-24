<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::get('/user', [UserController::class, 'user']);
    Route::post('/refresh', [UserController::class, 'refresh']);
    Route::post('/logout', [UserController::class, 'logout']);
});