<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Unified catalog search.
 *
 * - When SCOUT_DRIVER=meilisearch, queries are answered by Meilisearch (typo-tolerant,
 *   relevance-ranked over title/abstract/authors/keywords) and then hydrated + filtered.
 * - Otherwise it falls back to native MySQL FULLTEXT (MATCH ... AGAINST) with relevance
 *   ordering, so the app delivers real full-text search with zero extra services.
 */
class SearchService
{
    /**
     * @param  array<string, mixed>  $params  q, category, year, author, publisher, sort
     */
    public function search(array $params, int $perPage = 12): LengthAwarePaginator
    {
        $q = trim((string) ($params['q'] ?? ''));

        $query = Book::query()->published()->with(['authors', 'category', 'publisher']);

        $hasRelevance = false;

        if ($q !== '') {
            if (config('scout.driver') === 'meilisearch') {
                $hasRelevance = $this->applyMeilisearch($query, $q);
            } else {
                $hasRelevance = $this->applyFulltext($query, $q);
            }
        }

        $this->applyFilters($query, $params);
        $this->applySort($query, $params['sort'] ?? null, $hasRelevance);

        return $query->paginate($perPage)->withQueryString();
    }

    private function applyMeilisearch(Builder $query, string $q): bool
    {
        $ids = Book::search($q)->take(500)->keys()->all();

        if (empty($ids)) {
            $query->whereRaw('1 = 0');

            return false;
        }

        $query->whereIn('books.id', $ids)
            ->orderByRaw('FIELD(books.id, '.implode(',', $ids).')');

        return true; // order already set by Meilisearch relevance
    }

    private function applyFulltext(Builder $query, string $q): bool
    {
        $query->selectRaw(
            'books.*, MATCH(title, subtitle, abstract, description) AGAINST (? IN NATURAL LANGUAGE MODE) AS relevance',
            [$q]
        )->where(function (Builder $w) use ($q) {
            $w->whereFullText(['title', 'subtitle', 'abstract', 'description'], $q)
                ->orWhere('title', 'like', "%{$q}%")
                ->orWhere('isbn', 'like', "%{$q}%")
                ->orWhereHas('authors', fn (Builder $a) => $a->where('name', 'like', "%{$q}%"))
                ->orWhereHas('keywords', fn (Builder $k) => $k->where('name', 'like', "%{$q}%"));
        })->orderByDesc('relevance');

        return true;
    }

    private function applyFilters(Builder $query, array $params): void
    {
        if ($category = ($params['category'] ?? null)) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $category));
        }
        if ($year = ($params['year'] ?? null)) {
            $query->where('year', $year);
        }
        if ($author = ($params['author'] ?? null)) {
            $query->whereHas('authors', fn (Builder $q) => $q->where('slug', $author));
        }
        if ($publisher = ($params['publisher'] ?? null)) {
            $query->whereHas('publisher', fn (Builder $q) => $q->where('slug', $publisher));
        }
        if (! empty($params['open_access'])) {
            $query->where('is_open_access', true);
        }

        // Jenis koleksi: 'digital' (PDF/E-Book) atau 'cetak' (Hardcover/Paperback).
        match ($params['format'] ?? null) {
            'digital' => $query->whereIn('format', ['PDF', 'E-Book']),
            'cetak' => $query->whereIn('format', ['Hardcover', 'Paperback']),
            default => null,
        };
    }

    private function applySort(Builder $query, ?string $sort, bool $hasRelevance): void
    {
        // An explicit sort always wins; otherwise keep relevance order if present.
        match ($sort) {
            'popular' => $query->reorder()->orderByDesc('view_count'),
            'title' => $query->reorder()->orderBy('title'),
            'newest' => $query->reorder()->latest('published_at'),
            'oldest' => $query->reorder()->oldest('published_at'),
            default => $hasRelevance ? null : $query->latest('published_at'),
        };
    }
}
