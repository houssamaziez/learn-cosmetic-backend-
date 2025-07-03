<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Playlist;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

class PlaylistController extends Controller
{


    public function index(): JsonResponse
    {
        try {
            $playlists = Playlist::with(['category', 'courses'])->latest()->get()->map(function ($playlist) {
                $totalSeconds = (int) $playlist->courses->sum('video_duration');

                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                $seconds = $totalSeconds % 60;

                $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

                return [
                    'id'             => $playlist->id,
                    'title'          => $playlist->title,
                    'description'    => $playlist->description,
                    'image'          => $playlist->image ? asset($playlist->image) : null,
                    'courses_count'  => $playlist->courses->count(),
                    'total_duration' => $formattedDuration,
                    'category'       => [
                        'id'   => $playlist->category->id ?? null,
                        'name' => $playlist->category->name ?? null,
                    ],
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'Playlists retrieved successfully.',
                'data'    => $playlists,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve playlists.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function getByCategory(int $categoryId): JsonResponse
    {
        if (!Category::where('id', $categoryId)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        try {
            $playlists = Playlist::with(['category', 'courses'])
                ->where('category_id', $categoryId)
                ->latest()
                ->get()
                ->map(function ($playlist) {
                    $totalSeconds = (int) $playlist->courses->sum('video_duration');

                    // تحويل الثواني إلى صيغة HH:MM:SS
                    $hours = floor($totalSeconds / 3600);
                    $minutes = floor(($totalSeconds % 3600) / 60);
                    $seconds = $totalSeconds % 60;

                    $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

                    return [
                        'id'              => $playlist->id,
                        'title'           => $playlist->title,
                        'description'     => $playlist->description,
                        'image'           => $playlist->image ? asset($playlist->image) : null,
                        'courses_count'   => $playlist->courses->count(),
                        'total_duration'  => $formattedDuration, // صيغة الوقت النهائية
                        'category'        => [
                            'id'   => $playlist->category->id ?? null,
                            'name' => $playlist->category->name ?? null,
                        ],
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Playlists retrieved successfully.',
                'data'    => $playlists,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while retrieving playlists.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image'       => 'required|image|mimes:jpg,jpeg,png,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/playlists'), $imageName);
                $imagePath = 'uploads/playlists/' . $imageName;
            }

            $playlist = Playlist::create([
                'category_id' => $request->category_id,
                'title'       => $request->title,
                'description' => $request->description,
                'image'       => $imagePath,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Playlist created successfully.',
                'data'    => $playlist,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create playlist.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id): JsonResponse
    {
        try {
            $playlist = Playlist::with('category')->findOrFail($id);

            return response()->json([
                'status'  => true,
                'message' => 'Playlist retrieved successfully.',
                'data'    => [
                    'id'          => $playlist->id,
                    'title'       => $playlist->title,
                    'description' => $playlist->description,
                    'image_url'   => $playlist->image ? asset($playlist->image) : null,
                    'category'    => [
                        'id'   => $playlist->category->id ?? null,
                        'name' => $playlist->category->name ?? null,
                    ],
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Playlist not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve playlist.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
