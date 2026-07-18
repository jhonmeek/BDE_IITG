<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesUploads
{
    protected function storeUpload(?UploadedFile $file, string $directory, string $disk = 'public'): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store($directory, $disk);
    }

    protected function replaceUpload(?UploadedFile $file, ?string $currentPath, string $directory, string $disk = 'public'): ?string
    {
        if (! $file) {
            return $currentPath;
        }

        $this->deleteUpload($currentPath, $disk);

        return $this->storeUpload($file, $directory, $disk);
    }

    protected function deleteUpload(?string $path, string $disk = 'public'): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
