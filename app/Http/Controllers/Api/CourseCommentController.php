<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\Comment;

class CourseCommentController extends Controller
{
    public function store(Request $request,)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
            'course_id' => 'required|exists:courses,id',
        ]);

        $id = $request->input('course_id');

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // تحقق من وجود الكورس
            $course = Course::find($id);
            if (!$course) {
                return response()->json([
                    'status' => false,
                    'message' => 'Course not found.',
                ], 404);
            }

            // إنشاء التعليق
            $comment = Comment::create([
                'user_id'   => $request->user_id,
                'course_id' => $course->id,
                'content'   => $request->content,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Comment added successfully.',
                'data'    => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while adding the comment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function getCommentsByCourse($courseId)
    {
        $course = Course::with(['comments.user'])->find($courseId);

        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found.',
            ], 404);
        }

        $comments = $course->comments->map(function ($comment) {
            return [
                'id'        => $comment->id,
                'user_id'   => $comment->user_id,
                'user_name' => $comment->user->name ?? null,
                'content'   => $comment->content,
                'created_at' => $comment->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Comments retrieved successfully.',
            'data'    => $comments,
        ]);
    }
}
