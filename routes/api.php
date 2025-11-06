<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return "welcome from api";
})->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login'] )->name("login");
Route::post('/register', [AuthController::class, 'store'] )->name("register");

