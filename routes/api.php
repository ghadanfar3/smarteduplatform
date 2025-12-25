<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ProgressController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsStudent;
use App\Http\Middleware\IsTeacher;
use App\Http\Controllers\LessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\QuizController;

//انشاء الحساب وتسجيل الدخول والخروج للمستخدم
Route::post('/login', [AuthController::class, 'login'])->name("login");
Route::post('/register', [AuthController::class, 'register'])->name("register");

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name("logout")->middleware('auth:sanctum');
    Route::get('/courses/{courseId}/quiz', [QuizController::class, 'show']);
    Route::put('/edit-user/{id}', [AuthController::class , 'update']);
    Route::post('/lessons/{lesson}/complete', [ProgressController::class, 'completeLesson']);
//التعديل وحذف المستخدم
    Route::controller(AuthController::class)->middleware([IsAdmin::class])->group(function () {
        Route::post('delete-user/{id}', 'destroy');
        Route::get('/users', 'index') ;
    });

//التحكم في الكورس
    Route::controller(CourseController::class)->middleware([isTeacher::class])->group(function () {
        Route::post('/add-course', 'store')->name("add-course");
        Route::put('/edit-course/{id}', 'update')->name("edit-course");
        Route::post('delete-course/{id}', 'destroy')->name("delete-course");
    });


    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::get('/certificates/{id}/download', [CertificateController::class, 'getCertificateById']);
//مسارات أدارة الاختبارات
    Route::middleware([IsStudent::class])->group(function () {
        Route::post('/courses/{courseId}/quiz/submit', [QuizController::class, 'submit']);
        Route::post('/courses/{courseId}/reviews', [ReviewController::class, 'store']);
        Route::post('/courses/{id}/enroll', [EnrollmentController::class, 'enroll']);
        Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);
    });

//مسارات تقييم الدورة
    Route::get('/courses/{courseId}/reviews', [ReviewController::class, 'index']);

//عرض الدورات والدروس
    Route::get('/courses/{courseId}/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{id}', [LessonController::class, 'show']);
    Route::get('/courses', [CourseController::class, 'index'])->name("AllCourses");
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name("Courses");

//التحكم في الدروس
    Route::middleware([IsTeacher::class])->group(function () {
        Route::post('/courses/{courseId}/lessons', [LessonController::class, 'store']);
        Route::put('/lessons/{id}', [LessonController::class, 'update']);
        Route::delete('/lessons/{id}', [LessonController::class, 'destroy']);
        Route::post('/courses/{courseId}/quiz', [QuizController::class , 'store']);
        Route::put('/quizzes/{quiz}',[QuizController::class,'update']); // تعديل الاختبار
        Route::post('/quizzes/{quizId}', [QuizController::class ,'destroy']); // حذف الاختبار
    });


});
