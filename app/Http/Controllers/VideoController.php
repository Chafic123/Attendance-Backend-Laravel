<?php

namespace App\Http\Controllers;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as LaravelFFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
   public function encode(Request $request)
   {
   //     if (!$request->hasFile('video')) {
   //         return response()->json(['error' => 'No video uploaded'], 400);
   //     }
   
   //     $video = $request->file('video');
       
   //     if (!$video->isValid()) {
   //         return response()->json(['error' => 'Invalid video file'], 400);
   //     }
   
   //     dd($video->getRealPath(), $video->getClientOriginalName(), $video->getMimeType());
   
   //     try {
   //         $filename = time() . '.mp4';
   //         $path = storage_path('app/public/videos/' . $filename);
   
   //         LaravelFFMpeg::fromDisk('local') 
   //             ->open($video->getRealPath())
   //             ->export()
   //             ->toDisk('public')
   //             ->inFormat(new \FFMpeg\Format\Video\X264)
   //             ->save('videos/' . $filename);
   
   //         return response()->json([
   //             'message' => 'Video encoded successfully',
   //             'path' => asset('storage/videos/' . $filename)
   //         ]);
   //     } catch (\Exception $e) {
   //         return response()->json(['error' => 'Encoding failed: ' . $e->getMessage()], 500);
   //     }
   // }
}
}
