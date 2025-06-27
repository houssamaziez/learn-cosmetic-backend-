<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
