<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InstructorController extends Controller
{
    public function getCoursesForLoggedInInstructor()
    {
        $user = Auth::user();
        if (!$user->instructor) {
            return response()->json(['error' => 'User is not an instructor'], 403);
        }

        $courses = $user->instructor->courses()
            ->select('courses.id as course_id', 'courses.name', 'courses.Code')
            ->get();

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'No courses found for this instructor'], 404);
        }

        $coursesData = [];
        foreach ($courses as $course) {
            $coursesData[] = [
                'course_name' => $course->name,
                'course_code' => $course->Code ?? 'N/A',
            ];
        }

        return response()->json($coursesData);
    }

    public function getAllStudentsCourse($courseId, $returnJson = true)
    {
        $course = Course::find($courseId);
        if (!$course) {
            return $returnJson ? response()->json(['message' => 'Course not found'], 404) : [];
        }

        $students = $course->students()->with('user:id,first_name,last_name')->get();
        $studentsWithAttendance = [];

        foreach ($students as $student) {
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->whereHas('course_session', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })->get();

            $totalSessions = $attendanceRecords->count();
            $presentCount = $attendanceRecords->where('is_present', true)->count();
            $attendancePercentage = $totalSessions > 0
                ? round(($presentCount / $totalSessions) * 100, 2)
                : 0;

            $studentsWithAttendance[] = [
                'student_id' => $student->id,
                'first_name' => optional($student->user)->first_name,
                'last_name' => optional($student->user)->last_name,
                'major' => $student->major,
                'image' => $student->image,
                'video' => $student->video,
                'attendance_percentage' => $attendancePercentage
            ];
        }

        return $returnJson ? response()->json($studentsWithAttendance) : $studentsWithAttendance;
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'message' => 'required|string',
            'type' => 'required|string|in:Regular,Warning',
        ]);

        $instructor = Auth::user()->instructor;
        if (!$instructor) {
            return response()->json(['error' => 'User is not an instructor'], 403);
        }

        $notification = Notification::create([
            'student_id' => $request->student_id,
            'instructor_id' => $instructor->id,
            'course_id' => $request->course_id,
            'message' => $request->message,
            'type' => $request->type,
            'read_status' => false,
        ]);

        return response()->json(['message' => 'Notification sent successfully', 'notification' => $notification]);
    }


    public function updateInstructorProfile(Request $request)
    {
        $instructor = Auth::user()->instructor;
        $user = Auth::user();

        if (!$instructor) {
            return response()->json(['error' => 'Instructor not found'], 404);
        }

        $userValidator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        if ($userValidator->fails()) {
            return response()->json(['error' => $userValidator->errors()], 400);
        }

        if (!$user instanceof \App\Models\User) {
            return response()->json(['error' => 'Invalid user type'], 404);
        }

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');

        try {
            $user->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save user details: ' . $e->getMessage()], 500);
        }

        $instructorValidator = Validator::make($request->all(), [
            'phone_number' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($instructorValidator->fails()) {
            return response()->json(['error' => $instructorValidator->errors()], 400);
        }

        function sanitizeFileName($name)
        {
            $name = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
            return substr($name, 0, 50);
        }

        if ($request->hasFile('image')) {
            if ($instructor->image && Storage::disk('public')->exists($instructor->image)) {
                Storage::disk('public')->delete($instructor->image);
            }

            $firstName = sanitizeFileName($user->first_name);
            $lastName = sanitizeFileName($user->last_name);
            $imageName = 'profile_image_' . $firstName . '_' . $lastName . '_' . $instructor->id . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension();

            $imagePath = $request->file('image')->storeAs('profile_images', $imageName, 'public');
            $instructor->image = $imagePath;
        }

        $instructor->phone_number = $request->input('phone_number');

        try {
            $instructor->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save instructor details: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'image' => $instructor->image ? asset('storage/' . $instructor->image) : null,
        ]);
    }
}
