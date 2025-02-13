<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use App\Models\Student;
use App\Http\Controllers\Controller;

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
                'image' => $student->image,  // image is automatically Base64 encoded due to the mutator
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
}
