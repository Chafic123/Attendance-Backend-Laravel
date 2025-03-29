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
    // public function processVideo(Request $request)
    // {
    //     $user = Auth::user();

    //     $student = Student::where('user_id', $user->id)->first();

    //     if (!$student) {
    //         return response()->json(['error' => 'Student not found'], 404);
    //     }

    //     $videoRelativePath = $student->video;

    //     if (empty($videoRelativePath)) {
    //         return response()->json(['error' => 'Video path is missing in the database'], 404);
    //     }

    //     $videoPath = Storage::path('public/' . $videoRelativePath);

    //     if (!file_exists($videoPath)) {
    //         return response()->json(['error' => 'Video file not found'], 404);
    //     }

    //     $allowedExtensions = ['mp4', 'avi', 'mov'];
    //     $extension = pathinfo($videoPath, PATHINFO_EXTENSION);

    //     if (!in_array(strtolower($extension), $allowedExtensions)) {
    //         return response()->json(['error' => 'Invalid video format. Only MP4, AVI, and MOV are allowed.'], 400);
    //     }

    //     try {
    //         // Send the video to the ML 
    //         $response = Http::withHeaders([
    //             'Content-Type' => 'multipart/form-data',
    //         ])->attach(
    //             'video', fopen($videoPath, 'r'), basename($videoRelativePath)
    //         )->post('http://127.0.0.1:5000/process-video', [
    //             'student_id' => $student->student_id,
    //         ]);

    //         if ($response->successful()) {
    //             $results = $response->json();

    //             if (isset($results['success'])) {
    //                 return response()->json([
    //                     'student_id' => $student->student_id,
    //                     'video_url' => asset('storage/' . $videoRelativePath),
    //                     'results' => $results, 
    //                 ]);
    //             } else {
    //                 return response()->json(['error' => 'Unexpected response format from processing server.'], 500);
    //             }
    //         } else {
    //             Log::error('Failed to process video', [
    //                 'response_status' => $response->status(),
    //                 'response_body' => $response->body(),
    //                 'student_id' => $student->student_id
    //             ]);
    //             return response()->json(['error' => 'Failed to process video'], 500);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Error connecting to ML server', [
    //             'error_message' => $e->getMessage(),
    //             'student_id' => $student->student_id
    //         ]);
    //         return response()->json(['error' => 'Failed to connect to the processing server'], 500);
    //     }
    // }

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
