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
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\QuizController;

//انشاء الحساب وتسجيل الدخول والخروج للمستخدم
Route::post('/login', [AuthController::class, 'login'])->name("login");
Route::post('/register', [AuthController::class, 'register'])->name("register");

Route::middleware(['auth:sanctum'])->group(function () {
    //user
    Route::post('/logout', [AuthController::class, 'logout'])->name("logout");
    Route::put('/edit-user/{id}', [AuthController::class , 'update'])->name('update-info');
    //course
    Route::get('/courses', [CourseController::class, 'index'])->name("AllCourses");
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name("Courses");
    Route::get('/courses/{courseId}/quiz', [QuizController::class, 'show'])->name('Quiz');
    //lessons
    Route::get('/courses/{courseId}/lessons', [LessonController::class, 'index'])->name('All-lesssons');
    Route::get('/lessons/{id}', [LessonController::class, 'show'])->name('lesson');
    Route::post('/lessons/{lesson}/complete', [ProgressController::class, 'completeLesson'])->name('completed');
    //certificates
    Route::get('/certificates', [CertificateController::class, 'index'])->name('All-certificates');
    Route::get('/certificates/{id}', [CertificateController::class, 'show'])->name('certificate');
    Route::post('/courses/{course}/certificate', [CertificateController::class, 'create'])->name('add-certificate');
    Route::get('/courses/{courseId}/reviews', [ReviewController::class, 'index']);//عرض التقييمات


//التعديل وحذف المستخدم
    Route::controller(AuthController::class)->middleware([IsAdmin::class])->group(function () {
        Route::delete('delete-user/{id}', 'destroy');
        Route::get('/users', 'index') ;
    });



    Route::middleware([IsStudent::class])->group(function () {
        Route::post('/courses/{courseId}/quiz/submit', [QuizController::class, 'submit'])->name('Answer-Quiz');
        Route::post('/courses/{courseId}/reviews', [ReviewController::class, 'store'])->name('add-review');
        Route::post('/courses/{id}/enroll', [EnrollmentController::class, 'enroll'])->name('joinToCourse');
        Route::get('/my-courses', [EnrollmentController::class, 'myCourses'])->name('enroll-courses');
    });


    Route::middleware([IsTeacher::class])->group(function () {
        //Courses
        Route::post('/add-course', [CourseController::class,'store'])->name("add-course");
        Route::put('/edit-course/{id}', [CourseController::class,'update'])->name("edit-course");
        Route::delete('delete-course/{id}', [CourseController::class,'destroy'])->name("delete-course");
        //Lessons
        Route::post('/courses/{courseId}/lessons', [LessonController::class, 'store'])->name('add-lesson');
        Route::put('/lessons/{id}', [LessonController::class, 'update'])->name('edit-lesson');
        Route::delete('/lessons/{id}', [LessonController::class, 'destroy'])->name('delete-lesson');
        //Quiz
        Route::post('/courses/{courseId}/quiz', [QuizController::class , 'store'])->name('add-Quiz');
        Route::put('/quizzes/{quiz}',[QuizController::class,'update'])->name('edit-Quiz');
        Route::delete('/quizzes/{quizId}', [QuizController::class ,'destroy'])->name('delete-Quiz'); // حذف الاختبار
    });


});
