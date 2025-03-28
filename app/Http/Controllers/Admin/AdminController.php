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
use App\Models\Department;
use App\Models\Instructor;
use App\Models\Admin;

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
                    'User:id,first_name,last_name',
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
            ->select('id', 'first_name', 'last_name')
            ->paginate(12);

        return response()->json($instructors);
    }
    public function getCourseCalendar($courseId)
    {
        $course = Course::find($courseId);
        $sessions = CourseSession::where('course_id', $courseId)
            ->orderBy('date')
            ->get();
        // dd($sessions);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json($sessions);
    }

    /**
     * Get all students with their associated user data.
     */
    public function getAllStudents()
    {
        $students = Student::with([
            'user:id,first_name,last_name'
        ])
            ->select('id', 'user_id', 'major', 'image', 'video', 'student_id')
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

        $students = $course->students()->with('user:id,first_name,last_name')->get();

        $students = $students->map(function ($student) {
            return [
                'student_id' => $student->student_id,
                'first_name' => optional($student->user)->first_name,
                'last_name' => optional($student->user)->last_name,
                'major' => $student->major,
                'image' => $student->image,
                'video' => $student->video,
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

        $courses = $student->courses()->with(['instructors.user' => function ($query) {
            $query->select('users.id', 'users.first_name', 'users.last_name');
        }])->get();

        $coursesWithInstructors = $courses->map(function ($course) {
            $instructors = $course->instructors->map(function ($instructor) {
                return [
                    'instructor_name' => $instructor->user->first_name . ' ' . $instructor->user->last_name
                ];
            });

            return [
                'course_code' => $course->Code,
                'course_name' => $course->name,
                'section' => $course->Section,
                'instructors' => $instructors,
            ];
        });

        return response()->json($coursesWithInstructors);
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
            'phone_number' => $request->phone,
            'department_id' => $department->id,
        ]);

        return response()->json(['message' => 'Student updated successfully']);
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

    // Edit Course

    public function editCourse(Request $request, $courseId)
    {
        $validator = Validator::make($request->all(), [
            'Code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses')->where(fn($query) => $query->where('Section', $request->section))
                    ->ignore($courseId),
            ],
            'section' => 'required|integer', 
            'name' => 'required|string|max:255',
            'instructor_email' => 'required|email|exists:users,email',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'day_of_week' => 'required|string|max:255',
            'room' => 'nullable|string|max:255',
            'credits' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $user = User::where('email', $request->instructor_email)->first();
        if (!$user) {
            return response()->json(['message' => 'User with this email not found'], 404);
        }

        $instructor = Instructor::where('user_id', $user->id)->first();
        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found for this user'], 404);
        }

        $course->update([
            'Code' => $request->Code,
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'day_of_week' => $request->day_of_week,
            'Room' => $request->room,
            'Section' => $request->section, 
            'credits' => $request->credits,
        ]);

        $course->instructors()->sync([$instructor->id]);

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course,
            'instructor' => [
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->first_name . ' ' . $user->last_name,
            ]
        ], 200);
    }
}
