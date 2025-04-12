<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\Admin;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Get all courses with related instructors and departments.
     */
    public function getAllCourses()
    {
        $courses = Course::with([
            'instructors' => function ($query) {
                $query->with([
                    'User:id,first_name,last_name,email',
                    'Department:id,name'
                ]);
            },
        ])->paginate(12);

        return response()->json($courses);
    }

    /**
     * Get all instructors.
     */
    public function getAllInstructors()
    {
        $instructors = User::where('status', 'Instructor')
            ->with([
                'instructor.department:id,name'
            ])
            ->select('id', 'first_name', 'last_name', 'email')
            ->paginate(12);

        return response()->json($instructors);
    }
    // public function getCourseStudentCalendar($studentId , $courseId)
    // {
    //     $course = Course::find($courseId);
    //     $sessions = CourseSession::where('course_id', $courseId)
    //         ->orderBy('date')
    //         ->get();
    //     // dd($sessions);
    //     if (!$course) {
    //         return response()->json(['message' => 'Course not found'], 404);
    //     }

    //     return response()->json($sessions);
    // }

    /**
     * Get all students with their associated user data.
     */
    public function getAllStudents()
    {
        $students = Student::with([
            'user:id,first_name,last_name,email',
            'department:id,name'
        ])
            ->select('id', 'user_id', 'major', 'image', 'video', 'student_id', 'department_id')
            ->paginate(12);

        return response()->json($students);
    }

    // Admin details

    public function getAuthenticatedAdmin(Request $request)
    {
        $user = $request->user();

        $Admin = Admin::where('user_id', $user->id)->first();

        return response()->json([
            'user' => $user,
            'Admin' => $Admin
        ]);
    }

    /**
     * Get all students enrolled in a particular course.
     */
    public function getAllAdminStudentsCourse($courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $students = $course->students()->with('user:id,first_name,last_name,email')->get();

        $students = $students->map(function ($student) use ($course) {
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->whereHas('course_session', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->get();

            $absentCount = $attendanceRecords->where('is_present', false)->count();
            $absencePercentage = round($absentCount * 3.33, 2);

            $status = $absencePercentage >= 25 ? 'Drop risk' : 'Safe';

            return [
                'id'               => $student->id,
                'user_id'          => $student->user_id,
                'student_id'       => $student->student_id,
                'first_name'       => optional($student->user)->first_name,
                'last_name'        => optional($student->user)->last_name,
                'email'            => optional($student->user)->email,
                'department'       => optional($student->department)->name,
                'major'            => $student->major,
                'image'            => $student->image,
                'absence_percentage' => $absencePercentage . '%',
                'status'           => $status,
            ];
        });

        return response()->json($students);
    }



    /**
     * Get courses for a specific student, including instructor details.
     */
    public function getCoursesForStudent($studentId)
    {
        $student = Student::find($studentId);
    
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
    
        $courses = $student->courses()
            ->withPivot('status') 
            ->with(['instructors.user' => function ($query) {
                $query->select('users.id', 'users.first_name', 'users.last_name');
            }])
            ->get();
    
        $coursesWithInstructors = $courses->map(function ($course) use ($student) {
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->whereHas('course_session', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->get();
    
            $absentCount = $attendanceRecords->where('is_present', false)->count();
            $absencePercentage = round($absentCount * 3.33, 2);
            $riskStatus = $absencePercentage >= 25 ? 'Risk of drop' : 'Safe';
    
            return [
                'id' => $course->id,
                'course_code' => $course->Code,
                'course_name' => $course->name,
                'section' => $course->Section,
                'course_status' => $course->pivot->status, 
                'instructors' => $course->instructors->map(function ($instructor) {
                    return [
                        'instructor_name' => $instructor->user->first_name . ' ' . $instructor->user->last_name
                    ];
                }),
                'absence_percentage' => $absencePercentage . '%',
                'status' => $riskStatus,
            ];
        });
    
        return response()->json($coursesWithInstructors);
    }
    


    //delete student from course 

    public function deleteStudentFromCourse($courseId, $studentId)
    {
        $course = Course::find($courseId);
        $student = Student::find($studentId);

        if (!$course || !$student) {
            return response()->json(['error' => 'Course or student not found'], 404);
        }

        $course->students()->detach($studentId);

        return response()->json(['message' => 'Student removed from course successfully']);
    }

    /**
     * Get instructor details for a specific course and section.
     */
    public function getInstructorForCourseSection($courseId, $section)
    {
        $course = Course::where('id', $courseId)
            ->where('section', $section)
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course section not found'], 404);
        }

        $instructor = $course->instructors->first();
        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found for this section'], 404);
        }

        return response()->json([
            'instructor_name' => $instructor->user->first_name . ' ' . $instructor->user->last_name,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::user()->admin;
        $user = Auth::user();

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
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

        function sanitizeFileName($name)
        {
            $name = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
            return substr($name, 0, 50);
        }
        return response()->json([
            'message' => 'Profile updated successfully',
        ]);
    }


    // EditStudent 

    public function editStudent(Request $request, $studentId)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|exists:users,email',
            'phone' => 'required|string|max:15',
            'department' => 'required|string|max:255|exists:departments,name',
            'major' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::find($studentId);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $user = User::find($student->user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $department = Department::where('name', $request->department)->first();
        if (!$department) {
            return response()->json(['message' => 'Invalid department'], 400);
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        $student->update([
            'major' => $request->major,
            'phone_number' => $request->phone,
            'department_id' => $department->id,
        ]);

        return response()->json(['message' => 'Student updated successfully']);
    }

    //delete student 
    public function deleteStudent($studentId)
    {
        $student = Student::find($studentId);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $user = User::find($student->user_id);
        if ($user) {
            $user->delete();
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    // delete instructor

    public function deleteInstructor($instructorId)
    {
        $instructor = Instructor::find($instructorId);

        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], 404);
        }

        $instructor->delete();

        $user = User::find($instructor->user_id);

        if ($user) {
            $user->delete();
        }

        return response()->json(['message' => 'Instructor deleted successfully']);
    }

    // Edit Instructor
    public function editInstructor(Request $request, $instructorId)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|exists:users,email',
            'phone' => 'required|string|max:15',
            'department' => 'required|string|max:255|exists:departments,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $instructor = Instructor::find($instructorId);
        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], 404);
        }

        $department = Department::where('name', $request->department)->first();
        if (!$department) {
            return response()->json(['message' => 'Invalid department'], 400);
        }

        $user = User::find($instructor->user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        $instructor->update([
            'phone_number' => $request->phone,
            'department_id' => $department->id,
        ]);

        return response()->json(['message' => 'Instructor updated successfully']);
    }

    //Enroll student 
    public function enrollStudents(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $students = Student::whereIn('id', $request->student_ids)->get();

        foreach ($students as $student) {
            $student->courses()->syncWithoutDetaching([
                $request->course_id => [
                    'enrollment_date' => now()->toDateString(),
                    'status' => 'active'
                ]
            ]);
        }
        

        return response()->json(['message' => 'Students enrolled successfully']);
    }

    //Enroll Instructor 
    public function enrollInstructors(Request $request)
    {
        $request->validate([
            'instructor_ids' => 'required|array',
            'instructor_ids.*' => 'exists:instructors,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $courseId = $request->course_id;

        foreach ($request->instructor_ids as $instructorId) {
            $exists = DB::table('course_instructor')
                ->where('instructor_id', $instructorId)
                ->where('course_id', $courseId)
                ->exists();

            if (!$exists) {
                Instructor::findOrFail($instructorId)->courses()->attach($courseId);
            }
        }

        return response()->json(['message' => 'Instructors enrolled successfully']);
    }

    // Edit Course

    public function editCourse(Request $request, $courseId)
    {
        $messages = [
            'Code.unique' => 'A course session with this code and section already exists.',
            'instructor_email.exists' => 'Instructor email not found in our records.',
        ];

        $validator = Validator::make($request->all(), [
            'Code' => [
                'required',
                'string',
                Rule::unique('courses')->where(function ($query) use ($request) {
                    return $query->where('Section', $request->section);
                })->ignore($courseId),
            ],
            'section' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'instructor_email' => 'required|email|exists:users,email',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'day_of_week' => 'required|string|max:255',
            'room' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        // Check for room-time conflict (exc-luding the current course)
        $conflict = Course::where('Room', $request->room)
            ->where('day_of_week', $request->day_of_week)
            ->where('id', '!=', $courseId)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->first();

        if ($conflict) {
            return response()->json([
                'message' => 'Room is already busy at that time',
                'conflict_with' => $conflict
            ], 422);
        }

        $user = User::where('email', $request->instructor_email)->first();
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found for this user.'], 404);
        }

        $course->update([
            'Code' => $request->Code,
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'day_of_week' => $request->day_of_week,
            'Room' => $request->room,
            'Section' => $request->section,
            'credit' => $request->credits,
        ]);

        $course->instructors()->sync([$instructor->id]);

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course,
            'instructor' => [
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->first_name . ' ' . $user->last_name,
            ]
        ], 200);
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

    public function getStudentCalendar($studentId, $courseId)
    {
        $today = Carbon::today()->startOfDay();

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

    //delete a student from a specific course
    public function deleteStudentCourse($courseId, $studentId)
    {
        $course = Course::find($courseId);
        $student = Student::find($studentId);

        if (!$course || !$student) {
            return response()->json(['error' => 'Course or student not found'], 404);
        }

        $course->students()->detach($studentId);

        return response()->json(['message' => 'Student removed from course successfully']);
    }

    //generate a report for a specific student attendance 
    public function generateAttendanceReport($courseId, $studentId)
    {
        $course = Course::find($courseId);
        $student = Student::find($studentId);

        if (!$course || !$student) {
            return response()->json(['error' => 'Course or student not found'], 404);
        }
        $sessions = CourseSession::where('course_id', $courseId)->get();
        $attendances = Attendance::whereIn('course_session_id', $sessions->pluck('id'))
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('course_session_id');

        $sessions->map(function ($session) use ($attendances) {
            dd($attendances);
            $attendance = $attendances->get($session->id);
            return [
                'date' => $session->date,
                'status' => $attendance ? ($attendance->is_present ? 'Present' : 'Absent') : 'Absent',
                'attendance_id' => $attendance ? $attendance->id : null,
            ];
        });
    }

    // Attendance report for students in course

    public function downloadCourseAttendanceReport($courseId)
    {
        $course = Course::with([
            'students.user',
            'students.department',
            'instructors.user',
        ])->find($courseId);
    
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }
    
        $students = $course->students;
        $instructor = $course->instructors->first(); 
    
        $reportData = [];
        $totalAbsencePercentage = 0;
    
        foreach ($students as $student) {
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->whereHas('course_session', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->get();
    
            $absentCount = $attendanceRecords->where('is_present', false)->count();

            $absencePercentage = round($absentCount * 3.33, 2);
            $totalAbsencePercentage += $absencePercentage;

            $reportData[] = [
                'student_id' => $student->student_id,
                'first_name' => optional($student->user)->first_name,
                'last_name' => optional($student->user)->last_name,
                'email' => optional($student->user)->email,
                'department' => optional($student->department)->name ?? 'N/A',
                'major' => $student->major,
                'absence_percentage' => $absencePercentage,
                'status' => $absencePercentage >= 25 ? 'Drop Risk' : 'Safe',
            ];
        }
        $averageAbsence = count($students) ? round($totalAbsencePercentage / count($students), 2) : 0;
    
        return Pdf::loadView('reports.CourseAttendanceReport', [
            'course' => $course,
            'instructor' => $instructor, 
            'students' => $reportData,
            'averageAbsence' => $averageAbsence,
            'session' => $course->course_sessions->first(),
        ])->setPaper('A4', 'portrait')
        ->download("attendance_report_{$course->Code}.pdf");
    }
// student Courses
public function downloadStudentCoursesAttendanceReport($studentId)
{
    $student = Student::with(['user', 'department', 'courses.instructors.user'])->find($studentId);

    if (!$student) {
        return response()->json(['error' => 'Student not found'], 404);
    }

    $courses = $student->courses;
    $reportData = [];
    $totalAbsencePercentage = 0;

    foreach ($courses as $course) {
        $attendanceRecords = Attendance::where('student_id', $student->id)
            ->whereHas('course_session', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->get();

        $absentCount = $attendanceRecords->where('is_present', false)->count();
        $absencePercentage = round($absentCount * 3.33, 2);
        $totalAbsencePercentage += $absencePercentage;

        $reportData[] = [
            'course_code' => $course->Code,
            'course_name' => $course->name,
            'section' => $course->Section ?? 'N/A',
            'credits' => $course->credits ?? 'N/A',
            'instructor' => optional($course->instructors->first()?->user)->first_name . ' ' . optional($course->instructors->first()?->user)->last_name ?? 'N/A',
            'absence_percentage' => $absencePercentage,
            'status' => $absencePercentage >= 25 ? 'Risk of Drop ' : 'Safe',
        ];
    }

    $averageAbsence = count($courses) ? round($totalAbsencePercentage / count($courses), 2) : 0;
    $averageAttendance = round(100 - $averageAbsence, 2);

    return PDF::loadView('reports.Student_Courses_AttendanceReport', [
        'student' => $student,
        'courses' => $reportData,
        'averageAbsence' => $averageAbsence,
        'averageAttendance' => $averageAttendance
    ])
    ->setPaper('A4', 'portrait')
    ->download("student_attendance_report_{$student->student_id}.pdf");
}

}
