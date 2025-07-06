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

use App\Models\Playlist;

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
            ->withCount(['likes', 'comments']) // <-- احصل على عدد اللايكات والتعليقات
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($course) {
                return  [
                    'id'             => $course->id,
                    'title'          => $course->title,
                    'description'    => $course->description,
                    'image_path'     => $course->image_path ? asset($course->image_path) : null,
                    'video_path'     => $course->video_path ? asset($course->video_path) : null,
                    'video_duration' => $course->video_duration,
                    'is_watched'     => $course->is_watched,
                    'likes_count'    => $course->likes_count,
                    'comments_count' => $course->comments_count,
                    'playlist_id'    => $course->playlist_id,
                    'created_at'    => $course->created_at,
                    'updated_at'     => $course->updated_at,
                    'playlist'       => [
                        'id'          => $course->playlist->id,
                        'category_id' => $course->playlist->category_id,
                        'title'       => $course->playlist->title,
                        'image'       => $course->playlist->image,
                        'description' => $course->playlist->description,
                        'created_at'  => $course->playlist->created_at,
                        'updated_at'  => $course->playlist->updated_at,
                    ],
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $courses,
        ]);
    }


    public function getAllCoursesByPlaylist(int $playlistId): JsonResponse
    {
        if (!Playlist::where('id', $playlistId)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Playlist not found.',
            ], 404);
        }

        try {
            $courses = Course::with(['playlist'])
                ->withCount(['likes', 'comments'])
                ->where('playlist_id', $playlistId)
                ->latest()
                ->get()
                ->map(function ($course) {
                    $seconds = (int) $course->video_duration;
                    $hours = floor($seconds / 3600);
                    $minutes = floor(($seconds % 3600) / 60);
                    $remainingSeconds = $seconds % 60;
                    $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);

                    return [
                        'id'             => $course->id,
                        'title'          => $course->title,
                        'description'    => $course->description,
                        'image_path'     => $course->image_path ? asset($course->image_path) : null,
                        'video_path'     => $course->video_path ? asset($course->video_path) : null,
                        'video_duration' => $formattedDuration,
                        'is_watched'     => $course->is_watched,
                        'likes_count'    => $course->likes_count,
                        'comments_count' => $course->comments_count,
                        'playlist_id'    => $course->playlist_id,
                        'created_at'    => $course->created_at,
                        'updated_at'     => $course->updated_at,
                        'playlist'       => [
                            'id'          => $course->playlist->id,
                            'category_id' => $course->playlist->category_id,
                            'title'       => $course->playlist->title,
                            'image'       => $course->playlist->image,
                            'description' => $course->playlist->description,
                            'created_at'  => $course->playlist->created_at,
                            'updated_at'  => $course->playlist->updated_at,
                        ],
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Courses retrieved successfully.',
                'data'    => $courses,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while retrieving courses.',
                'error'   => $e->getMessage(),
            ], 500);
        }
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
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:6048',
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
