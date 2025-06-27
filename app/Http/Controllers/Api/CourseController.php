<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use FFMpeg;
use FFMpeg\FFMpeg as PHPFFMpeg;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFProbe;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Get limit and offset from query parameters, or use default values
        $limit = $request->query('limit', 10);   // Default: 10 items
        $offset = $request->query('offset', 0);  // Default: start from 0

        $courses = Course::with('playlist')
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $courses,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'playlist_id' => 'required|exists:playlists,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'video' => 'required|mimetypes:video/mp4,video/x-msvideo,video/quicktime|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Save image
            $imageName = uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $imagePath = $request->file('image')->storeAs('courses/images', $imageName, 'public');

            // Save video
            $videoName = uniqid() . '.' . $request->file('video')->getClientOriginalExtension();
            $videoPath = $request->file('video')->storeAs('courses/videos', $videoName, 'public');

            // Get video duration using ffprobe
            $duration = null;
            try {
                $ffprobe = FFProbe::create();
                $fullVideoPath = storage_path("app/public/{$videoPath}");
                $duration = $ffprobe->format($fullVideoPath)->get('duration');
            } catch (\Exception $e) {
                $duration = null; // fallback if ffprobe fails
            }

            $course = Course::create([
                'title'          => $request->title,
                'description'    => $request->description,
                'playlist_id'    => $request->playlist_id,
                'image_path'     => "storage/{$imagePath}",
                'video_path'     => "storage/{$videoPath}",
                'video_duration' => $duration,
                'is_watched'     => false,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Course created successfully.',
                'data'    => $course,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $course = Course::with('playlist')->find($id);

        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $course
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found.'
            ], 404);
        }

        $course->update($request->only('title', 'description', 'is_watched'));

        return response()->json([
            'status' => true,
            'message' => 'Course updated successfully.',
            'data' => $course
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found.'
            ], 404);
        }

        $course->delete();

        return response()->json([
            'status' => true,
            'message' => 'Course deleted successfully.'
        ]);
    }
}
