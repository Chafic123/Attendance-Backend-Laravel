<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseSessionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AddCourseController;
use App\Http\Controllers\Student\StudentController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth:sanctum')
        ->name('auth.logout');
});

// Admin Routes (Only Admins Can Access)
Route::middleware(['auth:sanctum', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('/instructors', [AdminController::class, 'getAllInstructors'])->name('admin.instructors');
    Route::get('/students', [AdminController::class, 'getAllStudents'])->name('admin.students');
    Route::get('/courses', [AdminController::class, 'getAllCourses'])->name('admin.courses');
    Route::get('/courses/{courseId}/students', [AdminController::class, 'getAllAdminStudentsCourse'])
        ->name('admin.course.students');

    Route::put('/profile', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
    Route::post('/courses', [AddCourseController::class, 'store'])->name('admin.course.add');
    
    Route::get('/students/{studentId}/courses', [AdminController::class, 'getCoursesForStudent'])
        ->name('admin.student.courses');

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('admin.user');
});

// Student Routes (Only Students Can Access)
Route::middleware(['auth:sanctum', 'role:Student'])->prefix('student')->group(function () {
    Route::get('/courses', [StudentController::class, 'getCoursesForLoggedInStudent'])->name('student.courses');
    Route::get('/notifications', [StudentController::class, 'getNotificationsForLoggedInStudent'])
        ->name('student.notifications');
    Route::get('/courses/{courseId}/calendar', [CourseSessionController::class, 'getSessionsWithAttendance'])
        ->name('student.attendance.sessions');
    Route::get('/schedule-report', [StudentController::class, 'getScheduleReportForLoggedInStudent'])
        ->name('student.schedule.report');
    Route::post('/profile', [StudentController::class, 'updateStudentProfile'])->name('student.profile.update');
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('student.user');

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('student.user');
});
