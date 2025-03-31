<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Models\Student;
use App\Models\CourseSession;

class MachineLearningController extends Controller
{
    public function processVideos()
    {
        $students = Student::where('processed_video', false)->get();

        if ($students->isEmpty()) {
            return response()->json(['error' => 'No students found with unprocessed videos'], 404);
        }

        return response()->json([
            'students' => $students->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'video_path' => $student->video,
                ];
            })
        ]);
    }

    public function updateProcessedStatus(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'processed' => 'required|in:0,1',
        ]);
    
        Log::info("Incoming data: ", $request->all());
    
        $student = Student::find($request->student_id);
        if (!$student) {
            Log::error("Student not found with ID: " . $request->student_id);
            return response()->json(['error' => 'Student not found'], 404);
        }
    
        Log::info("Updating student: " . $student->id);
        $student->processed_video = (string) $request->processed;  
        $student->save();
    
        Log::info("Student ID {$student->id} video processing status updated to: {$student->processed_video}");
    
        return response()->json(['message' => 'Processing status updated successfully']);
    }
    

    public function index()
    {
        // Normally
        $today = now()->format('Y-m-d');

        // FORCE Thursday 
        // $thursdayDate = now()->startOfWeek()->addDays(3)->format('Y-m-d'); // (Monday=0, Tuesday=1, Wednesday=2, Thursday=3)

        $courseSessions = CourseSession::with(['course', 'students.user'])
            ->whereDate('date', $today)
            ->get();

        return response()->json([
            'course_sessions' => $courseSessions->map(function ($session) {
                return [
                    'session_id' => $session->id,
                    'date' => $session->date,
                    'course_id' => $session->course_id,
                    'course_name' => $session->course->name,
                    'course_section' => $session->course->Section,
                    'start_time' => $session->course->start_time,
                    'end_time' => $session->course->end_time,
                    'students' => $session->students->map(function ($student) {
                        return [
                            'student_id' => $student->id,
                            'name' => $student->user->first_name . ' ' . $student->user->last_name,
                            // 'profile_video' => $student->video,
                        ];
                    }),
                ];
            })
        ]);
    }
}