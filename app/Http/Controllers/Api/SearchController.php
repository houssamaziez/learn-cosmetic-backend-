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

        $categories = Category::where('name', 'like', "%{$keyword}%")->get();
        $playlists  = Playlist::where('title', 'like', "%{$keyword}%")->get();
        $courses    = Course::where('title', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
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
