<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary;
use App\Models\Instructor;
use App\Events\StudentNotification;
use Carbon\Carbon;
use App\Models\CourseSession;

class InstructorController extends Controller
{
    public function getCoursesForLoggedInInstructor()
    {
        $user = Auth::user();
        if (!$user->instructor) {
            return response()->json(['error' => 'User is not an instructor'], 403);
        }

        $courses = $user->instructor->courses()
            ->select('courses.id as course_id', 'courses.name', 'courses.Code', 'courses.Section')
            ->get();

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'No courses found for this instructor'], 404);
        }

        $coursesData = [];
        foreach ($courses as $course) {
            $coursesData[] = [
                'course_id' => $course->course_id,
                'course_name' => $course->name,
                'course_section' => $course->Section,
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
                'Uni_id' => $student->student_id,
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
            'course_id' => 'required|exists:courses,id',
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
        broadcast(new StudentNotification(
            $request->student_id,
            $request->message,
            $request->course_id,
            $instructor->id
        ));

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


        $cloudinary = new Cloudinary();
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);

        if ($request->hasFile('image')) {
            try {
                $imageFile = $request->file('image');
                $uploadedImage = $cloudinary->uploadApi()->upload($imageFile->getRealPath(), [
                    'folder' => 'Instructors_Image',
                    'use_filename' => false,
                    'unique_filename' => false,
                    'overwrite' => true,
                    'resource_type' => 'image',
                ]);
                $instructor->image = $uploadedImage['secure_url'];
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to upload image: ' . $e->getMessage()], 500);
            }
        }

        $instructor->phone_number = $request->input('phone_number');

        try {
            $instructor->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save instructor details: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'image' => $instructor->image,
        ]);
    }


    public function getAuthenticatedStudent(Request $request)
    {
        $user = $request->user();

        $Instructor = Instructor::where('user_id', $user->id)->first();

        return response()->json([
            'user' => $user,
            'Instructor' => $Instructor
        ]);
    }

    // report 

    public function getScheduleReportForLoggedInInstructor()
    {
        $instructor = Auth::user()->instructor;

        if (!$instructor) {
            return response()->json(['error' => 'Instructor not logged in'], 401);
        }

        $scheduleReport = $instructor->courses()
            ->with([
                'terms',
            ])->get();
        $response = [
            'instructor' => [
                'instructor_id' => $instructor->id,
                'first_name' => $instructor->user->first_name,
                'last_name' => $instructor->user->last_name,
                'department' => $instructor->department->name ?? null,
                'email' => $instructor->user->email,
                'phone' => $instructor->phone_number,
            ],
            'courses' => $scheduleReport->map(function ($course) {
                $term = $course->terms->first();
                return [
                    'course_name' => $course->name,
                    'course_code' => $course->Code ?? 'N/A',
                    'credits' => $course->credits ?? 'N/A',
                    'room_name' => $course->Room ?? 'N/A',
                    'day_of_week' => $course->day_of_week ? str_split($course->day_of_week) : [],
                    'section_name' => $course->Section ?? 'N/A',
                    'time_start' => $course->start_time,
                    'time_end' => $course->end_time,
                    'term' => $term ? $term->name : 'N/A',
                    'year' => $term ? $term->year : 'N/A',
                ];
            }),
        ];

        return response()->json($response);
    }


    public function getCourseCalendar($courseId)
    {
        $course = Course::find($courseId);
        
        if (!$course) {
            return response()->json([
                'error' => 'Course not found'
            ], 404);
        }

        $sessions = CourseSession::where('course_id', $courseId)
            ->orderBy('date')
            ->get(['date']);

        $currentDate = Carbon::now()->toDateString();
        
        $enhancedSessions = $sessions->map(function ($session) use ($currentDate) {
            return [
                'date' => $session->date,
                'is_current_day' => $session->date === $currentDate,
                'day_name' => Carbon::parse($session->date)->format('l')
            ];
        });

        return response()->json([
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->Code,
            'total_sessions' => $sessions->count(),
            'current_date' => $currentDate, 
            'has_current_day' => $enhancedSessions->contains('is_current_day', true),
            'sessions' => $enhancedSessions
        ]);
    }

    public function getStudentCalendar($courseId, $studentId)
    {
        $today = Carbon::today()->startOfDay();

        $sessions = CourseSession::where('course_id', $courseId)
            ->orderBy('date')
            ->get();

        $attendances = Attendance::whereIn('course_session_id', $sessions->pluck('id'))
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('course_session_id');

        $calendarData = $sessions->map(function ($session) use ($attendances, $today) {
            $sessionDate = Carbon::parse($session->date)->startOfDay();
            $status = 'upcoming';
            $attendanceId = null;

            if ($sessionDate->lte($today)) {
                if ($attendances->has($session->id)) {
                    $status = $attendances[$session->id]->is_present ? 'present' : 'absent';
                    $attendanceId = $attendances[$session->id]->id;
                } else {
                    $status = 'absent';
                }
            }

            return [
                'id'     => $attendanceId,
                'date'   => $session->date,
                'status' => $status,
            ];
        });

        return response()->json($calendarData);
    }
    
}
