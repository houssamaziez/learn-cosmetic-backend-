<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class VideoHelper
{
    /**
     * Upload a video file to public/uploads/{folderName}
     */
    public static function upload(UploadedFile $video, string $folderName): string
    {
        $videoName = uniqid() . '.' . $video->getClientOriginalExtension();

        $targetPath = public_path("uploads/{$folderName}");

        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        $video->move($targetPath, $videoName);

        return "uploads/{$folderName}/{$videoName}";
    }
}
