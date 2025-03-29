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
    // public function processVideos()
    // {
    //     $students = Student::where('processed_video', false)->get();

    //     if ($students->isEmpty()) {
    //         return response()->json(['error' => 'No students found with unprocessed videos'], 404);
    //     }

    //     $responseData = [];

    //     foreach ($students as $student) {
    //         if (empty($student->video)) {
    //             continue;
    //         }

    //         $responseData[] = [
    //             'student_id' => $student->id,
    //             'message' => 'Processing failed before. Resending video.',
    //             'video_path' => $student->video
    //         ];
    //     }

    //     return response()->json($responseData, 200);
    // }
    public function processVideos()
    {
        $students = Student::where('processed_video', false)->get();

        if ($students->isEmpty()) {
            return response()->json(['error' => 'No students found with unprocessed videos'], 404);
        }

        $responseData = [];

        foreach ($students as $student) {
            if (empty($student->video)) {
                continue;
            }

            $mlResponse = Http::post('http://127.0.0.1:5000/processedvideo', [
                'student_id' => $student->id,
                'video_path' => $student->video  
            ]);

            if ($mlResponse->successful()) {
                $student->processed_video = true;
                $student->save();

                $responseData[] = [
                    'student_id' => $student->id,
                    'message' => 'Video sent to ML for processing',
                    'ml_response' => $mlResponse->json() 
                ];
            } else {
                $responseData[] = [
                    'student_id' => $student->id,
                    'error' => 'ML processing failed, please try again later'
                ];
            }
        }

        return response()->json($responseData, 200);
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

    //upload student video based on the student Id
    // public function uploadStudentVideo(Request $request, $studentId)
    // {
    //     $request->validate([
    //         'video' => 'required|mimes:mp4,avi,mov|max:51200', 
    //     ]);

    //     $student = Student::findOrFail($studentId);

    //     if ($request->hasFile('video')) {
    //         $videoPath = $request->file('video')->store('videos', 'public');
    //         $student->video = $videoPath;
    //         $student->save();

    //         return response()->json(['message' => 'Video uploaded successfully']);
    //     }

    //     return response()->json(['error' => 'No video file provided'], 400);
    // }
}
