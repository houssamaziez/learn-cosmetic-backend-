<?php
// app/Http/Controllers/Api/CourseLikeController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Like;
use App\Models\Course;

class CourseLikeController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $courseId = $request->input('course_id');
        $userId   = $request->input('user_id');

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json([
                'status'  => false,
                'message' => 'Course not found.',
            ], 404);
        }

        $existingLike = Like::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Like removed successfully.',
                'is_liked'    => false,

            ], 200);
        }

        $like = Like::create([
            'user_id'   => $userId,
            'course_id' => $courseId,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Course liked successfully.',
            'is_liked'    => true,
        ], 201);
    }
}
