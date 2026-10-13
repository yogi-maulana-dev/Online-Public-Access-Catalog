<?php

namespace App\Repositories;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Repositories\Contracts\BookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentBookRepository implements BookRepositoryInterface
{
    private function base(): Builder
    {
        return Book::query()
            ->published()
            ->with(['authors', 'category', 'publisher']);
    }

    public function findPublishedBySlug(string $slug): ?Book
    {
        return Book::query()
            ->published()
            ->with(['authors', 'category', 'publisher', 'keywords', 'files'])
            ->where('slug', $slug)
            ->first();
    }

    public function featured(int $limit = 6): Collection
    {
        return $this->base()
            ->featured()
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function latest(int $limit = 8): Collection
    {
        return $this->base()
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function popular(int $limit = 8): Collection
    {
        return $this->base()
            ->orderByDesc('view_count')
            ->orderByDesc('download_count')
            ->limit($limit)
            ->get();
    }

    public function paginateCatalog(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->base();

        if ($id = ($filters['category'] ?? null)) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $id)->orWhere('id', $id));
        }

        if ($year = ($filters['year'] ?? null)) {
            $query->where('year', $year);
        }

        if ($author = ($filters['author'] ?? null)) {
            $query->whereHas('authors', fn (Builder $q) => $q->where('slug', $author)->orWhere('authors.id', $author));
        }

        if ($publisher = ($filters['publisher'] ?? null)) {
            $query->whereHas('publisher', fn (Builder $q) => $q->where('slug', $publisher)->orWhere('publishers.id', $publisher));
        }

        if (! empty($filters['open_access'])) {
            $query->where('is_open_access', true);
        }

        match ($filters['sort'] ?? 'latest') {
            'popular' => $query->orderByDesc('view_count'),
            'title' => $query->orderBy('title'),
            'oldest' => $query->orderBy('published_at'),
            default => $query->latest('published_at'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function relatedTo(Book $book, int $limit = 6): Collection
    {
        $keywordIds = $book->keywords->pluck('id');

        return $this->base()
            ->whereKeyNot($book->id)
            ->where(function (Builder $q) use ($book, $keywordIds) {
                $q->where('category_id', $book->category_id)
                    ->when($keywordIds->isNotEmpty(), fn (Builder $q) => $q->orWhereHas(
                        'keywords', fn (Builder $kq) => $kq->whereIn('keywords.id', $keywordIds)
                    ));
            })
            ->limit($limit)
            ->get();
    }
}
