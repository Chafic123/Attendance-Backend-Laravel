<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AddCourseController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/instructors', [AdminController::class, 'getAllInstructors'])->name('admin.instructors');
    Route::get('/students', [AdminController::class, 'getAllStudents'])->name('admin.students');
    Route::get('/courses', [AdminController::class, 'getAllCourses'])->name('admin.courses');
    Route::get('/courses/{courseId}/students', [AdminController::class, 'getAllAdminStudentsCourse'])->name('admin.admin-student-courses');
    
    Route::post('/courses/add', [AddCourseController::class, 'store'])->name('admin.add-course');
    Route::get('/students/{studentId}/courses', [AdminController::class, 'getCoursesForStudent']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('admin.user');
});
