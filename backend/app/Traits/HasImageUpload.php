<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

trait HasImageUpload
{
    /**
     * Upload, resize to max 800px width, and convert to WebP.
     */
    protected function uploadImage(UploadedFile $file, string $folder, ?string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.default');

        $image = Image::read($file)
            ->scaleDown(width: 800)
            ->toWebp(85);

        $filename = uniqid() . '.webp';
        $path = "{$folder}/{$filename}";

        Storage::disk($disk)->put($path, (string) $image->toFilePointer(), 'public');

        return Storage::disk($disk)->url($path);
    }

    /**
     * Upload a document file (JPG, PNG, PDF) without resizing.
     */
    protected function uploadDocument(UploadedFile $file, string $folder, ?string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.default');

        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, [
            'disk' => $disk,
            'visibility' => 'public',
        ]);

        return Storage::disk($disk)->url($path);
    }
}
