<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;
use App\Models\Attendance;

class StudentController extends Controller
{
    public function getCoursesForLoggedInStudent()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $courses = $student->courses()->with('instructors.user')->get();

        $coursesWithInstructor = $courses->map(function ($course) use ($student) {
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->whereHas('course_session', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })->get();

            $totalSessions = $attendanceRecords->count();
            $absentCount = $attendanceRecords->where('is_present', false)->count();

            $attendancePercentage = $totalSessions > 0
                ? round((1 - ($absentCount / $totalSessions)) * 100, 2)
                : 100;

            return [
                'course_name' => $course->name,
                'course_code' => $course->code ?? 'N/A',
                'instructor_name' => optional($course->instructors->first())->user
                    ? $course->instructors->first()->user->first_name . ' ' . $course->instructors->first()->user->last_name
                    : 'No instructor assigned',
                'attendance_percentage' => $attendancePercentage
            ];
        });

        return response()->json($coursesWithInstructor);
    }

    public function getNotificationsForLoggedInStudent()
    {
        $student = Auth::user()->student;
        if (!$student) {
            return response()->json(['error' => 'Student not logged in'], 401);
        }

        $notifications = Notification::where('student_id', $student->id)
            ->with('instructor.user')
            ->get();

        $notificationsData = $notifications->map(function ($notification) {
            return [
                'message' => $notification->message,
                'type' => $notification->type,
                'instructor_name' => optional($notification->instructor)->user
                    ? $notification->instructor->user->first_name . ' ' . $notification->instructor->user->last_name
                    : 'No instructor assigned',
            ];
        });

        return response()->json($notificationsData);
    }

    public function updateStudentProfile(Request $request)
    {
        $student = Auth::user()->student;
        $user = Auth::user();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
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

        $studentValidator = Validator::make($request->all(), [
            'phone_number' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimes:mp4,avi,mov|max:10240',
        ]);

        if ($studentValidator->fails()) {
            return response()->json(['error' => $studentValidator->errors()], 400);
        }

        function sanitizeFileName($name)
        {
            $name = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
            return substr($name, 0, 50);
        }

        if ($request->hasFile('image')) {
            if ($student->image && Storage::disk('public')->exists($student->image)) {
                Storage::disk('public')->delete($student->image);
            }

            $firstName = sanitizeFileName($user->first_name);
            $lastName = sanitizeFileName($user->last_name);

            $imageName = 'profile_image_' . $firstName . '_' . $lastName . '_' . $student->student_id . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension();

            $imagePath = $request->file('image')->storeAs('profile_images', $imageName, 'public');
            $student->image = $imagePath;
        }

        if ($request->hasFile('video')) {
            if ($student->video && Storage::disk('public')->exists($student->video)) {
                Storage::disk('public')->delete($student->video);
            }

            $firstName = sanitizeFileName($user->first_name);
            $lastName = sanitizeFileName($user->last_name);

            $videoName = 'profile_video_' . $firstName . '_' . $lastName . '_' . $student->student_id . '_' . time() . '.' . $request->file('video')->getClientOriginalExtension();

            $videoPath = $request->file('video')->storeAs('profile_videos', $videoName, 'public');
            $student->video = $videoPath;
        }

        $student->phone_number = $request->input('phone_number');

        try {
            $student->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save student details: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'image' => $student->image ? asset('storage/' . $student->image) : null,
            'video' => $student->video ? asset('storage/' . $student->video) : null,
        ]);
    }


    public function getScheduleReportForLoggedInStudent()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return response()->json(['error' => 'Student not logged in'], 401);
        }

        $scheduleReport = $student->courses()
            ->with([
                'terms',
                'instructors.user:id,first_name,last_name',
            ])->get();

        $response = [
            'student' => [
                'student_id' => $student->student_id,
                'first_name' => $student->user->first_name,
                'last_name' => $student->user->last_name,
                'department' => $student->department->name ?? null,
                'email' => $student->user->email,
                'phone' => $student->phone,
                'major' => $student->major,
            ],
            'courses' => $scheduleReport->map(function ($course) {
                return [
                    'course_name' => $course->name,
                    'course_code' => $course->code ?? 'N/A',
                    'room_name' => $course->room ?? 'N/A',
                    'day_of_week' => str_split($course->day_of_week),
                    'section_name' => $course->section ?? 'N/A',
                    'time_start' => $course->start_time,
                    'time_end' => $course->end_time,
                    'term' => optional($course->terms->first())->name,
                    'year' => optional($course->terms->first())->year,
                    'term_start_at' => optional($course->terms->first())->start_time,
                    'term_end_at' => optional($course->terms->first())->end_time,
                    'instructors' => $course->instructors->map(function ($instructor) {
                        return [
                            'first_name' => $instructor->user->first_name,
                            'last_name' => $instructor->user->last_name,
                        ];
                    }),
                ];
            }),
        ];

        return response()->json($response);
    }
}
