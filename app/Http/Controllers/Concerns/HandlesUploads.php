<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesUploads
{
    protected function storeUpload(?UploadedFile $file, string $directory): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store($directory, 'public');
    }

    protected function replaceUpload(?UploadedFile $file, ?string $currentPath, string $directory): ?string
    {
        if (! $file) {
            return $currentPath;
        }

        $this->deleteUpload($currentPath);

        return $this->storeUpload($file, $directory);
    }

    protected function deleteUpload(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
