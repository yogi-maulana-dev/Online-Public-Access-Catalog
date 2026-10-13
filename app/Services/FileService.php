<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Store an uploaded file and attach a File record to the given model.
     */
    public function storeFor(
        Model $owner,
        UploadedFile $upload,
        string $type = 'manuscript',
        string $disk = 'local',
        bool $isPublic = false,
    ): File {
        $dir = $isPublic ? 'public/books' : 'protected/books';
        $path = $upload->store($dir, $disk);

        return $owner->files()->create([
            'uploaded_by' => auth()->id(),
            'type' => $type,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $upload->getClientOriginalName(),
            'mime_type' => $upload->getClientMimeType(),
            'size' => $upload->getSize(),
            'is_public' => $isPublic,
        ]);
    }

    public function delete(File $file): void
    {
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }
        $file->delete();
    }
}
