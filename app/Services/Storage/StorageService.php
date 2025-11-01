<?php

namespace App\Services\Storage;

use App\Models\FileShare;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    private function sanitizePath(string $path): string
    {
        // Remove path traversal attempts
        $path = str_replace(['../', '..\\'], '', $path);

        return ltrim($path, '/');
    }

    public function upload(string $path, $file)
    {
        return Storage::putFile($this->sanitizePath($path), $file);
    }

    public function getFiles(string $path)
    {
        return Storage::files($this->sanitizePath($path));
    }

    public function getFile(string $path)
    {
        return Storage::get($this->sanitizePath($path));
    }

    public function deleteFile(string $path)
    {
        return Storage::delete($this->sanitizePath($path));
    }

    public function downloadFile(string $path)
    {
        return Storage::download($this->sanitizePath($path));
    }

    public function shareFile(string $path, string $userId, string $sharedBy, string $permissions = 'read'): FileShare
    {
        return FileShare::create([
            'id' => Str::uuid(),
            'file_path' => $path,
            'user_id' => $userId,
            'shared_by' => $sharedBy,
            'permissions' => $permissions,
        ]);
    }

    // Mocked methods for now

    public function initMultipartUpload(string $path)
    {
        return ['message' => 'Multipart upload initialized.'];
    }

    public function uploadPart(string $path, string $partId, $file)
    {
        return ['message' => 'Part uploaded.'];
    }

    public function completeMultipartUpload(string $path, array $parts)
    {
        return ['message' => 'Multipart upload completed.'];
    }
}
