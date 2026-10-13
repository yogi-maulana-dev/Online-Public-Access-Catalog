<?php

namespace App\Repositories\Contracts;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BookRepositoryInterface
{
    public function findPublishedBySlug(string $slug): ?Book;

    public function featured(int $limit = 6): Collection;

    public function latest(int $limit = 8): Collection;

    public function popular(int $limit = 8): Collection;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateCatalog(array $filters, int $perPage = 12): LengthAwarePaginator;

    public function relatedTo(Book $book, int $limit = 6): Collection;
}
