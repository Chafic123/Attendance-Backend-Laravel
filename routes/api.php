<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
});


Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {

    Route::get('/instructors', [AdminController::class, 'getAllInstructors'])->name('admin.instructors');
    Route::get('/students', [AdminController::class, 'getAllStudents'])->name('admin.students');
    Route::get('/courses', [AdminController::class, 'getAllCourses'])->name('admin.courses');
    Route::get('/courses/{courseId}/students', [AdminController::class, 'getAllAdminStudentsCourse'])->name('admin.admin-student-courses');
    // //get InstructorStudentCourses
    // Route::get('/instructor-student-courses', [AdminController::class, 'getAllInstructorStudentCourses'])->name('admin.instructor-student-courses');
    // //get StudentAttendance
    // Route::get('/student-attendance', [AdminController::class, 'getAllStudentAttendance'])->name('admin.student-attendance');
    // //get InstructorAttendance
    // Route::get('/instructor-attendance', [AdminController::class, 'getAllInstructorAttendance'])->name('admin.instructor-attendance');
    // //get CourseAttendance
    // Route::get('/course-attendance', [AdminController::class, 'getAllCourseAttendance'])->name('admin.course-attendance');
    // //get StudentCourseAttendance

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('admin.user');
});
