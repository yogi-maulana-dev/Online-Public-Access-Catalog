<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

/**
 * Shared logic for the optional PDF fields (pdf_file / pdf_file_name / remove_pdf)
 * on the Book form — they are not model columns, so they are stripped from the
 * data array and applied to the polymorphic files() relation after save.
 */
trait HandlesBookPdf
{
    protected ?string $pdfPath = null;

    protected ?string $pdfOriginalName = null;

    protected bool $removePdf = false;

    /** @param array<string, mixed> $data */
    protected function extractPdfFields(array $data): array
    {
        $this->pdfPath = $data['pdf_file'] ?? null;
        $this->pdfOriginalName = $data['pdf_file_name'] ?? null;
        $this->removePdf = (bool) ($data['remove_pdf'] ?? false);

        unset($data['pdf_file'], $data['pdf_file_name'], $data['remove_pdf']);

        return $data;
    }

    protected function syncPdfFile(Book $book): void
    {
        $existing = $book->files()->where('type', 'pdf')->first();

        // Remove requested (and no replacement uploaded).
        if ($this->removePdf && ! $this->pdfPath) {
            if ($existing) {
                $this->deleteFileRecord($existing);
            }

            return;
        }

        if (! $this->pdfPath) {
            return; // nothing uploaded, keep as-is
        }

        // Replace: drop the old record/berkas first.
        if ($existing) {
            $this->deleteFileRecord($existing);
        }

        $book->files()->create([
            'uploaded_by' => auth()->id(),
            'type' => 'pdf',
            'disk' => 'local',
            'path' => $this->pdfPath,
            'original_name' => $this->pdfOriginalName ?: basename($this->pdfPath),
            'mime_type' => 'application/pdf',
            'size' => Storage::disk('local')->exists($this->pdfPath) ? Storage::disk('local')->size($this->pdfPath) : 0,
            'is_public' => $book->is_open_access,
            'watermark' => true,
        ]);
    }

    private function deleteFileRecord(\App\Models\File $file): void
    {
        // The seeded demo PDF is shared across books — only delete storage when unshared.
        $sharedElsewhere = \App\Models\File::where('path', $file->path)->whereKeyNot($file->id)->exists();
        if (! $sharedElsewhere && Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }
        $file->delete();
    }
}
