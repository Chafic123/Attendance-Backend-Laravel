<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Cloudinary\Cloudinary;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\CourseSession;
use App\Models\AttendanceRequest;
use App\Models\Course;

class StudentController extends Controller
{
    public function getCoursesForLoggedInStudent()
    {
        $user = Auth::user();

        if (!$user instanceof \App\Models\User) {
            return response()->json(['error' => 'Invalid user type'], 404);
        }
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

            $absentCount = $attendanceRecords->where('is_present', false)->count();

            $absencePercentage = round($absentCount * 3.33, 2);

            $status = $absencePercentage >= 25 ? 'At risk of drop' : 'Safe';

            return [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'course_code' => $course->Code ?? 'N/A',
                'instructor_name' => optional($course->instructors->first())->user
                    ? $course->instructors->first()->user->first_name . ' ' . $course->instructors->first()->user->last_name
                    : 'No instructor assigned',
                'absence_percentage' => $absencePercentage . '%',
                'status' => $status
            ];
        });

        return response()->json($coursesWithInstructor);
    }

    public function getNotificationsForLoggedInStudent(Request $request)
    {
        $student = Auth::user()->student;
        if (!$student) {
            return response()->json(['error' => 'Student not logged in'], 401);
        }

        $query = Notification::where('student_id', $student->id)
            ->with(['instructor.user', 'course']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        $notificationsData = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'message' => $notification->message,
                'type' => $notification->type,
                'read_status' => $notification->read_status,
                'instructor_name' => optional($notification->instructor)->user
                    ? $notification->instructor->user->first_name . ' ' . $notification->instructor->user->last_name
                    : 'No instructor assigned',
                'course' => optional($notification->course) ? [
                    'name' => $notification->course->name,
                    'code' => $notification->course->Code,
                    'start_time' => $notification->course->start_time,
                    'end_time' => $notification->course->end_time,
                    'day_of_week' => $notification->course->day_of_week,
                ] : null,
                'created_at' => $notification->created_at->toDateTimeString(),
            ];
        });

        return response()->json($notificationsData);
    }

    public function markNotificationAsRead($notificationId)
    {
        $student = Auth::user()->student;
        if (!$student) {
            return response()->json(['error' => 'Student not logged in'], 401);
        }

        $notification = Notification::where('id', $notificationId)
            ->where('student_id', $student->id)
            ->first();
        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->update(['read_status' => true]);

        return response()->json(['message' => 'Notification marked as read', 'notification' => $notification]);
    }

    // public function updateStudentProfile(Request $request)
    // {
    //     $student = Auth::user()->student;
    //     $user = Auth::user();

    //     if (!$student) {
    //         return response()->json(['error' => 'Student not found'], 404);
    //     }

    //     $userValidator = Validator::make($request->all(), [
    //         'first_name' => 'required|string|max:255',
    //         'last_name' => 'required|string|max:255',
    //     ]);

    //     if ($userValidator->fails()) {
    //         return response()->json(['error' => $userValidator->errors()], 400);
    //     }

    //     if (!$user instanceof \App\Models\User) {
    //         return response()->json(['error' => 'Invalid user type'], 404);
    //     }

    //     $user->first_name = $request->input('first_name');
    //     $user->last_name = $request->input('last_name');

    //     try {
    //         $user->save();
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to save user details: ' . $e->getMessage()], 500);
    //     }

    //     $studentValidator = Validator::make($request->all(), [
    //         'phone_number' => 'nullable|numeric',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'video' => 'nullable|mimes:mp4,avi,mov|max:10240',
    //     ]);

    //     if ($studentValidator->fails()) {
    //         return response()->json(['error' => $studentValidator->errors()], 400);
    //     }

    //     function sanitizeFileName($name)
    //     {
    //         $name = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
    //         return substr($name, 0, 50);
    //     }

    //     if ($request->hasFile('image')) {
    //         if ($student->image && Storage::disk('public')->exists($student->image)) {
    //             Storage::disk('public')->delete($student->image);
    //         }

    //         $firstName = sanitizeFileName($user->first_name);
    //         $lastName = sanitizeFileName($user->last_name);

    //         $imageName = 'profile_image_' . $firstName . '_' . $lastName . '_' . $student->student_id . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension();

    //         $imagePath = $request->file('image')->storeAs('profile_images', $imageName, 'public');
    //         $student->image = $imagePath;
    //     }

    //     if ($request->hasFile('video')) {
    //         if ($student->video && Storage::disk('public')->exists($student->video)) {
    //             Storage::disk('public')->delete($student->video);
    //         }

    //         $firstName = sanitizeFileName($user->first_name);
    //         $lastName = sanitizeFileName($user->last_name);

    //         $videoName = 'profile_video_' . $firstName . '_' . $lastName . '_' . $student->student_id . '_' . time() . '.' . $request->file('video')->getClientOriginalExtension();

    //         $videoPath = $request->file('video')->storeAs('profile_videos', $videoName, 'public');
    //         $student->video = $videoPath;
    //     }

    //     $student->phone_number = $request->input('phone_number');

    //     try {
    //         $student->save();
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to save student details: ' . $e->getMessage()], 500);
    //     }

    //     return response()->json([
    //         'message' => 'Profile updated successfully',
    //     ]);
    // }
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

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');

        try {
            if ($user instanceof \App\Models\User) {
                $user->save();
            } else {
                return response()->json(['error' => 'Invalid user type'], 500);
            }
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

        // Cloudinary
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
                    'folder' => 'Students_Image',
                    'use_filename' => false,
                    'unique_filename' => false,
                    'overwrite' => true,
                    'resource_type' => 'image',
                ]);
                $student->image = $uploadedImage['secure_url'];
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to upload image: ' . $e->getMessage()], 500);
            }
        }

        if ($request->hasFile('video')) {
            try {
                if ($student->video) {
                    $oldVideoPublicId = basename(parse_url($student->video, PHP_URL_PATH));
                    // dd($oldVideoPublicId);
                    // Delete old video 
                    $cloudinary->uploadApi()->destroy($oldVideoPublicId);
                }

                $videoFile = $request->file('video');
                $uploadedVideo = $cloudinary->uploadApi()->upload($videoFile->getRealPath(), [
                    'folder' => 'Videos',
                    'use_filename' => false,
                    'unique_filename' => false,
                    'overwrite' => true,
                    'resource_type' => 'video',
                ]);

                $student->video = $uploadedVideo['secure_url'];
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to upload video: ' . $e->getMessage()], 500);
            }
        }

        $student->phone_number = $request->input('phone_number');

        try {
            $student->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save student details: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'image' => $student->image,
            'video' => $student->video,
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
                'phone' => $student->phone_number,
                'major' => $student->major,
            ],
            'courses' => $scheduleReport->map(function ($course) {
                return [
                    'course_name' => $course->name,
                    'course_code' => $course->Code ?? 'N/A',
                    'room_name' => $course->Room ?? 'N/A',
                    'day_of_week' => str_split($course->day_of_week),
                    'section_name' => $course->Section ?? 'N/A',
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

    public function getAuthenticatedStudent(Request $request)
    {
        $user = $request->user();

        $student = Student::where('user_id', $user->id)->first();

        return response()->json([
            'user' => $user,
            'student' => $student
        ]);
    }

    //calender

    //original
    // public function getStudentCalendar($courseId, $studentId)
    // {
    //     $today = Carbon::today();
    //     $sessions = CourseSession::where('course_id', $courseId)
    //         ->orderBy('date')
    //         ->get();

    //     $attendances = Attendance::whereIn('course_session_id', $sessions->pluck('id'))
    //         ->where('student_id', $studentId)
    //         ->get()
    //         ->keyBy('course_session_id');

    //     $calendarData = $sessions->map(function ($session) use ($attendances, $today) {
    //         $sessionDate = Carbon::parse($session->date);
    //         $status = 'upcoming';

    //         if ($sessionDate->lte($today)) {
    //             $status = $attendances->has($session->id)
    //                 ? ($attendances[$session->id]->is_present ? 'present' : 'absent')
    //                 : 'absent';
    //         }

    //         return [
    //             'date'   => $session->date,
    //             'status' => $status,
    //         ];
    //     });

    //     return response()->json($calendarData);
    // }

    //tester
    public function getStudentCalendar($courseId, $studentId)
{
    $isEnrolled = DB::table('course_student')
        ->where('course_id', $courseId)
        ->where('student_id', $studentId)
        ->exists();

    if (!$isEnrolled) {
        return response()->json(['message' => 'Student not enrolled in this course.'], 403);
    }

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


    // request correction 

    public function requestCorrection(Request $request, $attendanceId)
    {
        $student = Auth::user()->student;

        if (!$student) {
            return response()->json(['error' => 'Unauthorized request'], 403);
        }

        $attendance = Attendance::where('id', $attendanceId)
            ->where('student_id', $student->id)
            ->first();

        if (!$attendance) {
            return response()->json(['error' => 'Attendance record not found'], 404);
        }

        $courseSession = CourseSession::find($attendance->course_session_id);
        if (!$courseSession) {
            return response()->json(['error' => 'Course session not found'], 404);
        }

        $course = Course::find($courseSession->course_id);
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $instructor = $course->instructors()->first();
        // dd($instructor->id);
        if (!$instructor) {
            return response()->json(['error' => 'Instructor not found'], 404);
        }
        AttendanceRequest::create([
            'student_id' => $student->id,
            'attendance_id' => $attendance->id,
            'course_id' => $courseSession->course_id,
            'instructor_id' => $instructor->id,
            'reason' => $request->input('reason'),
            'request_date' => now(),
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Correction request submitted successfully']);
    }

    public function deleteStudentImage()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $cloudinary = new Cloudinary();
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);

        try {
            if ($student->image) {
                $imagePublicId = basename(parse_url($student->image, PHP_URL_PATH));
                $cloudinary->uploadApi()->destroy($imagePublicId);
                $student->image = null;
                $student->save();
            }

            return response()->json([
                'message' => 'Student image deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete image: ' . $e->getMessage()], 500);
        }
    }

    public function deleteStudentVideo()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $cloudinary = new Cloudinary();
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);

        try {
            if ($student->video) {
                // Extracting the public ID from the video URL
                $videoPublicId = basename(parse_url($student->video, PHP_URL_PATH));

                // Destroying the video on Cloudinary
                $cloudinary->uploadApi()->destroy($videoPublicId);

                $student->video = null;
                $student->save();
            }

            return response()->json([
                'message' => 'Student video deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete video: ' . $e->getMessage()], 500);
        }
    }

    // public function deleteStudentVideo(Request $request)
    // {
    //     $student = Auth::user()->student;

    //     if (!$student) {
    //         return response()->json(['error' => 'Student not found'], 404);
    //     }

    //     $cloudinary = new Cloudinary();
    //     $cloudinary = new Cloudinary([
    //         'cloud' => [
    //             'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    //             'api_key' => env('CLOUDINARY_API_KEY'),
    //             'api_secret' => env('CLOUDINARY_API_SECRET'),
    //         ],
    //     ]);

    //     try {
    //         if ($student->video) {
    //             $videoPublicId = basename(parse_url($student->video, PHP_URL_PATH));
    //             $cloudinary->uploadApi()->destroy($videoPublicId);
    //             $student->video = null;
    //             $student->save();
    //         }

    //         return response()->json([
    //             'message' => 'Student video deleted successfully.',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to delete video: ' . $e->getMessage()], 500);
    //     }
    // }

    //generate schedule report 
    public function downloadScheduleReport()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return response()->json(['error' => 'Student not logged in'], 401);
        }

        $scheduleReport = $student->courses()
            ->with([
                'terms',
                'instructors.user:id,first_name,last_name',
            ])
            ->get();

        $studentData = [
            'student_id' => $student->student_id,
            'first_name' => $student->user->first_name,
            'last_name' => $student->user->last_name,
            'department' => $student->department->name ?? null,
            'email' => $student->user->email,
            'phone' => $student->phone_number,
            'major' => $student->major,
        ];

        $coursesData = $scheduleReport->map(function ($course) {
            return [
                'course_name' => $course->name,
                'course_code' => $course->Code ?? 'N/A',
                'credits' => $course->credits ?? 'N/A',
                'room_name' => $course->Room ?? 'N/A',
                'day_of_week' => str_split($course->day_of_week),
                'section_name' => $course->Section ?? 'N/A',
                'time_start' => $course->start_time,
                'time_end' => $course->end_time,
                'term' => optional($course->terms->first())->name,
                'year' => optional($course->terms->first())->year,
                'instructors' => $course->instructors->map(function ($instructor) use ($course) {
                    // If there's extra info in the pivot you need (like main instructor), you can use it here
                    return [
                        'first_name' => $instructor->user->first_name,
                        'last_name' => $instructor->user->last_name,
                        'pivot_role' => $instructor->pivot->role ?? null, // example if you have it
                    ];
                }),
            ];
        });

        $pdf = Pdf::loadView('reports.StudentSchedule', [
            'student' => $studentData,
            'courses' => $coursesData,
        ])->setPaper('a4', 'landscape'); // <-- Wider layout

        return $pdf->download('schedule_report.pdf');
    }
}
