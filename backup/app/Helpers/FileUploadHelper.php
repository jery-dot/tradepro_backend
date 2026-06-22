<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileUploadHelper
{
    /**
     * Upload a single file to a given directory and optionally delete old file.
     *
     * @param UploadedFile|null $file       The uploaded file instance
     * @param string            $directory  Directory inside public/ (e.g. 'upload/user_images')
     * @param string|null       $oldFile    Old file name (to delete after new upload)
     * @param string|null       $prefix     Optional prefix for filename
     *
     * @return string|null                 New filename or null if no file uploaded
     */
    public static function upload(?UploadedFile $file, string $directory, ?string $oldFile = null, ?string $prefix = null): ?string
    {
        if (! $file) {
            return $oldFile; // nothing uploaded; keep previous file
        }

        // Ensure directory exists in public path
        $uploadPath = public_path($directory);
        if (! File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        // Build a unique filename: prefix_timestamp_random.ext
        $extension = $file->getClientOriginalExtension();
        $namePart  = $prefix ? Str::slug($prefix).'_' : '';
        $filename  = $namePart.time().'_'.Str::random(6).'.'.$extension;

        // Move file
        $file->move($uploadPath, $filename);

        // Delete old file if provided and different
        if ($oldFile && $oldFile !== $filename) {
            self::delete($directory, $oldFile);
        }

        return $filename;
    }

    /**
     * Delete a file from a given public directory.
     *
     * @param string      $directory Directory inside public/ (e.g. 'upload/user_images')
     * @param string|null $filename  File name to delete
     */
    public static function delete(string $directory, ?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path($directory.DIRECTORY_SEPARATOR.$filename);

        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
