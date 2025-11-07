<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return "welcome from api";
})->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login'] )->name("login");
Route::post('/register', [AuthController::class, 'register'] )->name("register");
Route::controller(CourseController::class)->group(function (){
    Route::get('/courses','index')->name("Courses") ;
    Route::post('/addcourse','store')->name("addcourse") ;

});


