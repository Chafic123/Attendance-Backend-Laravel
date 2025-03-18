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

        return response()->json($students); // image will be Base64 due to the mutator in the model
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
}
