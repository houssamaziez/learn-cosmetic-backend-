<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class PromotionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'is_active'   => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $fileName = uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/promotions'), $fileName);
            $imagePath = 'uploads/promotions/' . $fileName;
        }

        $promotion = Promotion::create([
            'title'       => $request->title,
            'description' => $request->description,
            'image'       => $imagePath,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'is_active'   => $request->is_active ?? true,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Promotion created successfully.',
            'data'    => $promotion,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        // Optional query parameters: ?active=true&limit=10
        $query = Promotion::query();

        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        $limit = $request->get('limit', 20); // Default to 20 items per page

        $promotions = $query->orderBy('start_date', 'desc')->paginate($limit);

        return response()->json([
            'status' => true,
            'message' => 'Promotions fetched successfully.',
            'data' => $promotions,
        ]);
    }
}
