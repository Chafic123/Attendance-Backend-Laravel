<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;

class MachineLearningController extends Controller
{
    public function processVideo(Request $request)
    {
        $user = Auth::user();

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $videoRelativePath = $student->video;

        if (empty($videoRelativePath)) {
            return response()->json(['error' => 'Video path is missing in the database'], 404);
        }

        $videoPath = Storage::path('public/' . $videoRelativePath);

        if (!file_exists($videoPath)) {
            return response()->json(['error' => 'Video file not found'], 404);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->attach(
                'video', fopen($videoPath, 'r'), basename($videoRelativePath)
            )->post('http://friends-machine-ip:5000/process-video', [
                'student_id' => $student->student_id,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'student_id' => $student->student_id,
                    'video_url' => asset('storage/' . $videoRelativePath),
                    'results' => $response->json(),
                ]);
            } else {
                return response()->json(['error' => 'Failed to process video'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to connect to the processing server'], 500);
        }
    }
}