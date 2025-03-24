<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AddCourseController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Instructor\InstructorController;
use App\Http\Controllers\MachineLearningController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');
    Route::get('/course-sessions', [MachineLearningController::class, 'index'])->name('student.course_sessions.index');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth:sanctum')
        ->name('auth.logout');
    Route::post('/register', [AuthenticatedSessionController::class, 'register'])->name('auth.register');
    
});

// Admin Routes (Only Admins Can Access)
Route::middleware(['auth:sanctum', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('/instructors', [AdminController::class, 'getAllInstructors'])->name('admin.instructors');
    Route::get('/students', [AdminController::class, 'getAllStudents'])->name('admin.students');
    Route::get('/courses', [AdminController::class, 'getAllCourses'])->name('admin.courses');
    Route::get('/courses/{courseId}/students', [AdminController::class, 'getAllAdminStudentsCourse'])
        ->name('admin.course.students');
    Route::get('/{courseId}/calender', [AdminController::class, 'getCourseCalendar']);

    Route::post('/profile', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
    Route::post('/Addcourse', [AddCourseController::class, 'store'])->name('admin.course.add');

    Route::get('/students/{studentId}/courses', [AdminController::class, 'getCoursesForStudent'])
        ->name('admin.student.courses');

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('admin.user');
});

// Instructor Routes (Only Instructors Can Access)

Route::middleware(['auth:sanctum', 'role:Instructor'])->prefix('instructor')->group(function () {

    Route::get('/courses', [InstructorController::class, 'getCoursesForLoggedInInstructor'])->name('instructor.courses');
    Route::get('/courses/{courseId}/students', [InstructorController::class, 'getAllStudentsCourse']);
    //send notification
    Route::post('/courses/send-notification', [InstructorController::class, 'sendNotification'])
        ->name('instructor.course.student.notification');
    Route::post('/profile', [InstructorController::class, 'updateInstructorProfile'])->name('Instructor.profile.update');
    Route::get('/schedule-report', [InstructorController::class, 'getScheduleReportForLoggedInInstructor'])
        ->name('Instructor.schedule.report');
    Route::get('/user', [InstructorController::class, 'getAuthenticatedStudent'])->name('Instructor.user');

});

// Student Routes (Only Students Can Access)
Route::middleware(['auth:sanctum', 'role:Student'])->prefix('student')->group(function () {
    Route::get('/courses', [StudentController::class, 'getCoursesForLoggedInStudent'])->name('student.courses');
    Route::get('/notifications', [StudentController::class, 'getNotificationsForLoggedInStudent'])
        ->name('student.notifications');
    Route::get('/courses/{courseId}/{studentId}/calendar', [StudentController::class, 'getStudentCalendar'])
        ->name('student.attendance.sessions');
    Route::put('/notifications/{notificationId}/read', [StudentController::class, 'markNotificationAsRead'])
        ->name('student.notification.read');
    Route::get('/schedule-report', [StudentController::class, 'getScheduleReportForLoggedInStudent'])
        ->name('student.schedule.report');
    Route::post('/profile', [StudentController::class, 'updateStudentProfile'])->name('student.profile.update');
    Route::get('/user', [StudentController::class, 'getAuthenticatedStudent'])->name('student.user');
    // Route::post('/upload-video', [MachineLearningController::class, 'processVideo'])->name('student.video.upload');
    Route::post('/attendance-requests/{attendanceId}', [StudentController::class, 'requestCorrection'])->name('student.attendance.request');
});
