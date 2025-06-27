<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageHelper
{
    /**
     * Upload an image to public/uploads/{folderName}
     */
    public static function upload(UploadedFile $image, string $folderName): string
    {
        $imageName = uniqid() . '.' . $image->getClientOriginalExtension();

        $targetPath = public_path("uploads/{$folderName}");

        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        $image->move($targetPath, $imageName);

        return "uploads/{$folderName}/{$imageName}";
    }
}
