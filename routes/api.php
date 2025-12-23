<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\VideoController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsTeacher;
use App\Http\Controllers\LessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//انشاء الحساب وتسجيل الدخول والخروج للمستخدم
Route::post('/login', [AuthController::class, 'login'] )->name("login");
Route::post('/register', [AuthController::class, 'register'] )->name("register");
Route::post('/logout', [AuthController::class, 'logout'])->name("logout")->middleware('auth:sanctum') ;
//التعديل وحذف المستخدم
Route::controller(AuthController::class)->middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    Route::put('/edit-user/{id}',  'update');
    Route::post('delete-user/{id}',  'destroy');
});
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
    Route::get('/certificates/{id}/download', [CertificateController::class, 'getCertificateById']);
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


//عرض الدورات والدروس
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/courses/{courseId}/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{id}', [LessonController::class, 'show']);
    Route::get('/courses',[CourseController::class, 'index'])->name("AllCourses") ;
    Route::get('/courses/{id}',[CourseController::class, 'show'])->name("Courses") ;
});
//التحكم في الدروس
Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    Route::post('/courses/{courseId}/lessons', [LessonController::class, 'store']);
    Route::put('/lessons/{id}', [LessonController::class, 'update']);
    Route::delete('/lessons/{id}', [LessonController::class, 'destroy']);
});

Route::post('/upload-video', [VideoController::class, 'store']);
Route::post('/courses/{course}/image', [CourseController::class, 'uploadImage']);





