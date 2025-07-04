<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Playlist;
use App\Models\Course;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->query('keyword');

        if (!$keyword) {
            return response()->json([
                'status' => false,
                'message' => 'Keyword is required.',
            ], 400);
        }

        $categories = Category::where('name', 'like', "%{$keyword}%")
            ->take(4)
            ->get();

        $playlists = Playlist::with(['category', 'courses'])
            ->where('title', 'like', "%{$keyword}%")
            ->take(3)
            ->get()
            ->map(function ($playlist) {
                $totalSeconds = (int) $playlist->courses->sum('video_duration');
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                $seconds = $totalSeconds % 60;

                return [
                    'id'             => $playlist->id,
                    'title'          => $playlist->title,
                    'description'    => $playlist->description,
                    'image'          => $playlist->image ? asset($playlist->image) : null,
                    'courses_count'  => $playlist->courses->count(),
                    'total_duration' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
                    'category'       => [
                        'id'   => $playlist->category->id ?? null,
                        'name' => $playlist->category->name ?? null,
                    ],
                ];
            });

        $courses = Course::with('playlist')
            ->where('title', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->take(3)
            ->get();

        return response()->json([
            'status'     => true,
            'keyword'    => $keyword,
            'categories' => $categories,
            'playlists'  => $playlists,
            'courses'    => $courses,
        ]);
    }
}
