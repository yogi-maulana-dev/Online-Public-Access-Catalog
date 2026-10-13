<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\BookService;
use App\Services\CitationService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    public function __construct(private BookService $books)
    {
    }

    public function show(Book $book, Request $request, RecommendationService $recommender, CitationService $citations)
    {
        abort_unless($book->status->value === 'published', 404);

        $book->load(['authors', 'category', 'publisher', 'keywords', 'files']);
        $this->books->recordView($book, $request, $request->query('from', 'catalog'));

        return view('books.show', [
            'book' => $book,
            'similar' => $recommender->similar($book, 6),
            'citations' => $citations->all($book),
            'pdf' => $book->pdfFile(),
        ]);
    }

    /**
     * Online PDF reader with overlay watermark + download protection.
     */
    public function read(Book $book, Request $request)
    {
        abort_unless($book->status->value === 'published', 404);
        $file = $book->pdfFile();
        abort_unless($file, 404, 'Berkas PDF tidak tersedia.');

        return view('books.read', [
            'book' => $book,
            'file' => $file,
            'streamUrl' => route('books.stream', $book->slug),
        ]);
    }

    /**
     * Stream the raw PDF bytes inline (never as an attachment) for the viewer.
     * Download protection: requires auth, inline disposition, no caching.
     */
    public function stream(Book $book, Request $request): StreamedResponse
    {
        abort_unless($book->status->value === 'published', 404);
        $file = $book->pdfFile();
        abort_unless($file && Storage::disk($file->disk)->exists($file->path), 404);

        return Storage::disk($file->disk)->response($file->path, $file->original_name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$file->original_name.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Controlled download: only allowed for public files; logs the download.
     */
    public function download(Book $book, Request $request): StreamedResponse
    {
        abort_unless($book->status->value === 'published', 404);
        $file = $book->pdfFile();
        abort_unless($file && Storage::disk($file->disk)->exists($file->path), 404);

        // Protected files require authentication.
        if (! $file->is_public && ! $request->user()) {
            abort(403, 'Silakan masuk untuk mengunduh berkas ini.');
        }

        $this->books->recordDownload($book, $file, $request);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
