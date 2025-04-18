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
            'message' => 'nullable|string'
        ]);

        Log::info("Incoming data: ", $request->all());

        $student = Student::find($request->student_id);
        if (!$student) {
            Log::error("Student not found with ID: " . $request->student_id);
            return response()->json(['error' => 'Student not found'], 404);
        }

        if ($request->processed == '1') {
            $student->processed_video = '1';
            $student->save();
            Log::info("Student ID {$student->id} video processing status updated to: {$student->processed_video}");
        } else {
            try {
                if ($student->video && Storage::exists($student->video)) {
                    Storage::delete($student->video);
                    Log::info("Deleted video for student ID: {$student->id}");
                }

                $student->video = null;
                $student->processed_video = '0';
                $student->save();

                $notificationMessage = $request->message ?? 'Video processing failed (not enough frames detected)';
                
                \App\Models\Notification::create([
                    'student_id' => $student->id,
                    'course_id' => null,
                    'instructor_id' => null,
                    'message' => $notificationMessage,
                    'type' => 'Warning',
                    'read_status' => false,
                    'data' => [
                        'original_message' => $request->message,
                        'required_frames' => config('ml.minimum_frames', 300),
                        'student_name' => $student->user->name ?? 'Unknown',
                    ]
                ]);

                Log::info("Reset video status and created notification for student ID: {$student->id}");
            } catch (\Exception $e) {
                Log::error("Error cleaning up failed video for student {$student->id}: " . $e->getMessage());
                return response()->json([
                    'error' => 'Failed to clean up video',
                    'details' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Processing status updated successfully',
            'student' => [
                'id' => $student->id,
                'processed_video' => $student->processed_video,
                'has_video' => !empty($student->video)
            ]
        ]);
    }

    public function submitAttendance(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:course_sessions,id',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.is_present' => 'required|boolean'
        ]);

        try {
            foreach ($validated['attendance'] as $record) {
                \App\Models\Attendance::updateOrCreate(
                    [
                        'course_session_id' => $validated['session_id'],
                        'student_id' => $record['student_id']
                    ],
                    [
                        'is_present' => $record['is_present'],
                        'attended_at' => $record['is_present'] ? now() : null
                    ]
                );
            }

            return response()->json(['message' => 'Attendance recorded successfully']);
        } catch (\Exception $e) {
            Log::error("Attendance error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to record attendance'], 500);
        }
    }

    public function index()
    {
        $today = now()->format('Y-m-d');

        $processedStudents = Student::where('processed_video', 1)
            ->with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'name' => $student->user->first_name . ' ' . $student->user->last_name,
                    'university_id' => $student->student_id,
                ];
            });

        $courseSessions = CourseSession::with(['course', 'students'])
            ->whereDate('date', $today)
            ->get()
            ->map(function ($session) {
                return [
                    'session_id' => $session->id,
                    'date' => $session->date,
                    'course_id' => $session->course_id,
                    'course_name' => $session->course->name,
                    'course_section' => $session->course->Section,
                    'start_time' => $session->course->start_time,
                    'end_time' => $session->course->end_time,
                    'students' => $session->students->pluck('id')
                ];
            });

        return response()->json([
            'course_sessions' => $courseSessions,
            'processed_students' => $processedStudents
        ]);
    }
}
