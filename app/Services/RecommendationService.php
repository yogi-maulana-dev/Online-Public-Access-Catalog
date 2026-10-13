<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookView;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Content-based recommendations (no external AI dependency):
 * similarity is computed from shared category, keywords and authors.
 */
class RecommendationService
{
    /**
     * Books similar to the given one, ranked by a simple content-overlap score.
     */
    public function similar(Book $book, int $limit = 6): Collection
    {
        $book->loadMissing('keywords', 'authors');
        $keywordIds = $book->keywords->pluck('id');
        $authorIds = $book->authors->pluck('id');

        return Book::query()
            ->published()
            ->with(['authors', 'category'])
            ->whereKeyNot($book->id)
            ->where(function (Builder $q) use ($book, $keywordIds, $authorIds) {
                $q->where('category_id', $book->category_id)
                    ->when($keywordIds->isNotEmpty(), fn (Builder $q) => $q->orWhereHas(
                        'keywords', fn (Builder $k) => $k->whereIn('keywords.id', $keywordIds)
                    ))
                    ->when($authorIds->isNotEmpty(), fn (Builder $q) => $q->orWhereHas(
                        'authors', fn (Builder $a) => $a->whereIn('authors.id', $authorIds)
                    ));
            })
            ->withCount([
                'keywords as keyword_overlap' => fn (Builder $k) => $k->whereIn('keywords.id', $keywordIds),
            ])
            ->orderByRaw('(category_id = ?) DESC', [$book->category_id])
            ->orderByDesc('keyword_overlap')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Personalised recommendations from a reader's view/download history.
     * Falls back to popular books for anonymous or new users.
     */
    public function forUser(?User $user, int $limit = 8): Collection
    {
        if (! $user) {
            return $this->popularFallback($limit);
        }

        // Categories & keywords derived from what the user has engaged with.
        $seenBookIds = $user->downloads()->pluck('book_id')
            ->merge(BookView::where('user_id', $user->id)->pluck('book_id'))
            ->unique();

        if ($seenBookIds->isEmpty()) {
            return $this->popularFallback($limit);
        }

        $categoryIds = Book::whereIn('id', $seenBookIds)->pluck('category_id')->filter()->unique();
        $keywordIds = DB::table('book_keyword')->whereIn('book_id', $seenBookIds)->pluck('keyword_id')->unique();

        $recs = Book::query()
            ->published()
            ->with(['authors', 'category'])
            ->whereNotIn('id', $seenBookIds)
            ->where(function (Builder $q) use ($categoryIds, $keywordIds) {
                $q->whereIn('category_id', $categoryIds)
                    ->when($keywordIds->isNotEmpty(), fn (Builder $q) => $q->orWhereHas(
                        'keywords', fn (Builder $k) => $k->whereIn('keywords.id', $keywordIds)
                    ));
            })
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();

        return $recs->isNotEmpty() ? $recs : $this->popularFallback($limit);
    }

    private function popularFallback(int $limit): Collection
    {
        return Book::query()
            ->published()
            ->with(['authors', 'category'])
            ->orderByDesc('view_count')
            ->orderByDesc('download_count')
            ->limit($limit)
            ->get();
    }
}
