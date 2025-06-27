<?php

namespace App\Http\Controllers\Api\CategoryController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Category::all();

            return response()->json([
                'status'  => true,
                'message' => 'Categories retrieved successfully.',
                'data'    => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve categories.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */

    public function create(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000',
                'icon' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $iconPath = null;
            if ($request->hasFile('icon')) {
                $iconName = uniqid() . '.' . $request->file('icon')->getClientOriginalExtension();
                $request->file('icon')->move(public_path('uploads/categories'), $iconName);
                $iconPath = 'uploads/categories/' . $iconName;
            }

            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $iconPath,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Category created successfully.',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        try {
            return response()->json([
                'status'  => true,
                'message' => 'Category retrieved successfully.',
                'data'    => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve the category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'icon'        => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Handle icon update
            if ($request->hasFile('icon')) {
                // Delete old icon if exists
                if ($category->icon && File::exists(public_path($category->icon))) {
                    File::delete(public_path($category->icon));
                }

                $iconName = uniqid() . '.' . $request->file('icon')->getClientOriginalExtension();
                $request->file('icon')->move(public_path('uploads/categories'), $iconName);
                $category->icon = 'uploads/categories/' . $iconName;
            }

            // Update other fields
            $category->name        = $request->name;
            $category->description = $request->description;
            $category->save();

            return response()->json([
                'status'  => true,
                'message' => 'Category updated successfully.',
                'data'    => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Update failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
