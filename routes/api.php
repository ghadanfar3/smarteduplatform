<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'] )->name("login");
Route::post('/register', [AuthController::class, 'register'] )->name("register");
Route::post('/logout', [AuthController::class, 'logout'])->name("logout")->middleware('auth:sanctum') ;

//التحكم في الكورس
Route::controller(CourseController::class)->middleware(['auth:sanctum', isTeacher::class])->group(function (){
    Route::post('/add-course','store')->name("add-course") ;
    Route::put('/edit-course/{id}', 'update')->name("edit-course") ;
    Route::post('delete-course/{id}' , 'destroy')->name("delete-course") ;
});


Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::post('/courses/{id}/enroll', [EnrollmentController::class, 'enroll']);
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::get('/certificates/{id}/download', [CertificateController::class, 'download']);
});

use App\Http\Controllers\QuizController;
//مسارات أدارة الاختبارات
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/courses/{courseId}/quiz', [QuizController::class, 'show']);
    Route::post('/courses/{courseId}/quiz/submit', [QuizController::class, 'submit']);
});

use App\Http\Controllers\ReviewController;
//مسارات تقييم الدورة
Route::middleware(['auth:sanctum', 'role:student'])->post('/courses/{courseId}/reviews', [ReviewController::class, 'store']);
Route::get('/courses/{courseId}/reviews', [ReviewController::class, 'index']);

use App\Http\Controllers\LessonController;
//عرض الدورات والدروس
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/courses/{courseId}/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{id}', [LessonController::class, 'show']);
    Route::get('/courses','index')->name("Courses") ;
    Route::get('/courses/{id}','show')->name("Courses") ;
});
//التحكم في الدروس
Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    Route::post('/courses/{courseId}/lessons', [LessonController::class, 'store']);
    Route::put('/lessons/{id}', [LessonController::class, 'update']);
    Route::delete('/lessons/{id}', [LessonController::class, 'destroy']);
});



