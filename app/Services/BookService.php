<?php

namespace App\Services;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Models\BookView;
use App\Models\Download;
use App\Models\File;
use Illuminate\Http\Request;

class BookService
{
    /**
     * Record a (throttled) view for analytics and bump the denormalized counter.
     */
    public function recordView(Book $book, Request $request, string $source = 'catalog'): void
    {
        $sessionKey = "viewed_book_{$book->id}";
        if ($request->session()->has($sessionKey)) {
            return;
        }
        $request->session()->put($sessionKey, now()->timestamp);

        BookView::create([
            'book_id' => $book->id,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'source' => $source,
        ]);

        $book->increment('view_count');
    }

    /**
     * Record a download and bump the counter.
     */
    public function recordDownload(Book $book, ?File $file, Request $request): void
    {
        Download::create([
            'book_id' => $book->id,
            'file_id' => $file?->id,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $book->increment('download_count');
    }

    /**
     * Publish a book and make it searchable.
     */
    public function publish(Book $book): Book
    {
        $book->forceFill([
            'status' => BookStatus::Published,
            'published_at' => $book->published_at ?? now(),
            'year' => $book->year ?? now()->year,
        ])->save();

        $book->searchable();

        return $book;
    }

    public function unpublish(Book $book): Book
    {
        $book->forceFill(['status' => BookStatus::Archived])->save();
        $book->unsearchable();

        return $book;
    }
}
