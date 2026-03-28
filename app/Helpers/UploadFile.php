<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UploadFile
{
    /**
     * Upload image
     * @param UploadedFile $file
     * @param string $type
     * @param int|null $width
     * @param int|null $height
     * @param bool|null $aspectRatio
     * @return string|null
     */
    public function uploadImage(
        UploadedFile $file,
        string $type,
        int $id,
        ?int $width = null,
        ?int $height = null,
        ?bool $aspectRatio = true
    ): ?string {
        try {
            $time = now()->timestamp;
            $fileName = Str::random(10) . '_' . $time . '.webp';
            $path = "public/images/{$type}/$id";

            // Create image instance
            $image = Image::make($file);

            // Resize image if width or height is set
            if ($width || $height) {
                $image->resize($width, $height, function ($constraint) use ($aspectRatio) {
                    if ($aspectRatio) {
                        $constraint->aspectRatio();
                    }
                    $constraint->upsize();
                });
            }

            // Convert to WebP and save image
            Storage::put("{$path}/{$fileName}", $image->encode('webp', config('const.IMAGE_QUALITY')));

            return Storage::url("{$path}/{$fileName}");
        } catch (\Exception $e) {
            Log::error('Upload image error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete old image
     * @param string $imageUrl
     * @return void
     */
    public function deleteOldImage(string $imageUrl): void
    {
        try {
            $path = str_replace('/storage/', '', $imageUrl);
            if (Storage::exists('public/' . $path)) {
                Storage::delete('public/' . $path);
            }
        } catch (\Exception $e) {
            Log::error('Delete old image error: ' . $e->getMessage());
        }
    }
}
