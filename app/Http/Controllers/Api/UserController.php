<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
// No code to execute here, please remove the incorrect file path.
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use App\Helpers\ImageHelper; // Assuming you have an ImageHelper for image uploads
use App\Helpers\VideoHelper; // Assuming you have a VideoHelper for video uploads
class UserController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        // Inline validation
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'User registered successfully.',
                'data'    => $user,
            ], 201);

        } catch (Exception $e) {
            Log::error('User registration error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'An unexpected error occurred.',
                'error'   => app()->isProduction() ? 'Server error' : $e->getMessage(),
            ], 500);
        }
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'token' => $token,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

}


public function getMe(Request $request)
{
    return response()->json([
        'status' => true,
        'user' => $request->user(),
    ]);
}


public function uploadImage(Request $request): JsonResponse
{
    $validated = $request->validate([
        'image' => 'required|image|max:2048', // Max 2MB
    ]);

    try {
        $image = $request->file('image');

        // Use a helper to upload the image to the 'users' folder
        $path = ImageHelper::upload($image, 'users');

        return response()->json([
            'status'  => true,
            'message' => 'Image uploaded successfully.',
            'path'    => $path,
        ], 201);

    } catch (\Throwable $e) {
        Log::error('Image upload failed', ['error' => $e->getMessage()]);

        return response()->json([
            'status'  => false,
            'message' => 'Image upload failed.',
            'error'   => 'Internal Server Error',
        ], 500);
    }
}
    public function uploadVideo(Request $request): JsonResponse
    {
        $request->validate([
            'video' => 'required|mimetypes:video/mp4,video/x-msvideo,video/quicktime|max:51200', // 50MB max
        ]);

        try {
            $path = VideoHelper::upload($request->file('video'), 'videos');

            return response()->json([
                'status' => true,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
